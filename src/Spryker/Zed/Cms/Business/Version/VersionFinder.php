<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Cms\Business\Version;

use ArrayObject;
use Generated\Shared\Transfer\CmsVersionCollectionTransfer;
use Generated\Shared\Transfer\CmsVersionCriteriaTransfer;
use Generated\Shared\Transfer\CmsVersionDataTransfer;
use Generated\Shared\Transfer\CmsVersionTransfer;
use Orm\Zed\Cms\Persistence\Map\SpyCmsVersionTableMap;
use Orm\Zed\Cms\Persistence\SpyCmsPage;
use Propel\Runtime\ActiveQuery\Criteria;
use Spryker\Zed\Cms\Business\Exception\MissingPageException;
use Spryker\Zed\Cms\Business\Version\Mapper\VersionDataMapperInterface;
use Spryker\Zed\Cms\Persistence\CmsQueryContainerInterface;

class VersionFinder implements VersionFinderInterface
{
    protected const string COLUMN_NAME_DATA = 'Data';

    /**
     * @var \Spryker\Zed\Cms\Persistence\CmsQueryContainerInterface
     */
    protected $queryContainer;

    /**
     * @var \Spryker\Zed\Cms\Business\Version\Mapper\VersionDataMapperInterface
     */
    protected $versionDataMapper;

    /**
     * @var array<\Spryker\Zed\CmsExtension\Dependency\Plugin\CmsVersionTransferExpanderPluginInterface>
     */
    protected $transferExpanderPlugins;

    /**
     * @param \Spryker\Zed\Cms\Persistence\CmsQueryContainerInterface $queryContainer
     * @param \Spryker\Zed\Cms\Business\Version\Mapper\VersionDataMapperInterface $versionDataMapper
     * @param array<\Spryker\Zed\CmsExtension\Dependency\Plugin\CmsVersionTransferExpanderPluginInterface> $transferExpanderPlugins
     */
    public function __construct(
        CmsQueryContainerInterface $queryContainer,
        VersionDataMapperInterface $versionDataMapper,
        array $transferExpanderPlugins
    ) {
        $this->queryContainer = $queryContainer;
        $this->versionDataMapper = $versionDataMapper;
        $this->transferExpanderPlugins = $transferExpanderPlugins;
    }

    public function findLatestCmsVersionByIdCmsPage(int $idCmsPage): ?CmsVersionTransfer
    {
        $cmsVersionEntity = $this->queryContainer->queryCmsVersionByIdPage($idCmsPage)->findOne();

        return $this->getCmsVersionTransfer($cmsVersionEntity);
    }

    /**
     * @param int $idCmsPage
     *
     * @return array<\Generated\Shared\Transfer\CmsVersionTransfer>
     */
    public function findAllCmsVersionByIdCmsPage(int $idCmsPage): array
    {
        return $this->findAllCmsVersionByIdCmsPageWithColumns(
            $idCmsPage,
            SpyCmsVersionTableMap::getFieldNames(),
        );
    }

    public function findCmsVersionByIdCmsPageAndVersion(int $idCmsPage, int $version): ?CmsVersionTransfer
    {
        $cmsVersionEntity = $this->queryContainer->queryCmsVersionByIdPageAndVersion($idCmsPage, $version)->findOne();

        return $this->getCmsVersionTransfer($cmsVersionEntity);
    }

    /**
     * @param array<int> $cmsVersionIds
     *
     * @return \ArrayObject<int, \Generated\Shared\Transfer\CmsVersionTransfer>
     */
    public function findCmsVersionsByIds(array $cmsVersionIds): ArrayObject
    {
        $cmsVersionTransfers = new ArrayObject();
        $cmsVersionCollection = $this->queryContainer->queryAllCmsVersions()
            ->filterByIdCmsVersion($cmsVersionIds, Criteria::IN)
            ->find();

        foreach ($cmsVersionCollection as $cmsVersionEntity) {
            $cmsVersionTransfers->append($this->getCmsVersionTransfer($cmsVersionEntity));
        }

        return $cmsVersionTransfers;
    }

    public function getCmsVersionCollection(CmsVersionCriteriaTransfer $cmsVersionCriteriaTransfer): CmsVersionCollectionTransfer
    {
        $idCmsPage = $cmsVersionCriteriaTransfer->getCmsVersionConditionsOrFail()->getIdCmsPageOrFail();

        $selectColumns = SpyCmsVersionTableMap::getFieldNames();

        if ($cmsVersionCriteriaTransfer->getCmsVersionConditionsOrFail()->getIsContentLoaded() !== true) {
            $selectColumns = array_diff(
                $selectColumns,
                [static::COLUMN_NAME_DATA],
            );
        }

        $cmsVersionCollectionTransfer = new CmsVersionCollectionTransfer();

        $cmsVersionCollectionTransfer->getCmsVersions()
            ->exchangeArray($this->findAllCmsVersionByIdCmsPageWithColumns(
                $idCmsPage,
                $selectColumns,
            ));

        return $cmsVersionCollectionTransfer;
    }

    /**
     * @param \Orm\Zed\Cms\Persistence\SpyCmsVersion|null $cmsVersionEntity
     *
     * @return \Generated\Shared\Transfer\CmsVersionTransfer|null
     */
    protected function getCmsVersionTransfer($cmsVersionEntity): ?CmsVersionTransfer
    {
        if ($cmsVersionEntity === null) {
            return null;
        }

        $cmsVersionTransfer = $this->versionDataMapper->mapToCmsVersionTransfer($cmsVersionEntity);

        return $this->expandCmsVersionTransfer($cmsVersionTransfer);
    }

    protected function expandCmsVersionTransfer(CmsVersionTransfer $cmsVersionTransfer): CmsVersionTransfer
    {
        foreach ($this->transferExpanderPlugins as $transferExpanderPlugin) {
            $cmsVersionTransfer = $transferExpanderPlugin->expandTransfer($cmsVersionTransfer);
        }

        return $cmsVersionTransfer;
    }

    /**
     * @param array<string> $selectColumns
     *
     * @return array<\Generated\Shared\Transfer\CmsVersionTransfer>
     */
    protected function findAllCmsVersionByIdCmsPageWithColumns(int $idCmsPage, array $selectColumns): array
    {
        $cmsVersionRows = $this->queryContainer->queryCmsVersionByIdPage($idCmsPage)
            ->select($selectColumns)
            ->find();

        $cmsVersionTransfers = [];
        foreach ($cmsVersionRows as $cmsVersionRow) {
            $cmsVersionTransfer = $this->versionDataMapper->mapCmsVersionDataArrayToCmsVersionTransfer(
                $cmsVersionRow,
                new CmsVersionTransfer(),
            );
            $cmsVersionTransfers[] = $this->expandCmsVersionTransfer($cmsVersionTransfer);
        }

        return $cmsVersionTransfers;
    }

    public function getCmsVersionData(int $idCmsPage): CmsVersionDataTransfer
    {
        $cmsPageEntity = $this->getCmsPage($idCmsPage);
        $cmsVersionDataTransfer = $this->versionDataMapper->mapToCmsVersionDataTransfer($cmsPageEntity);

        return $cmsVersionDataTransfer;
    }

    /**
     * @param int $idCmsPage
     *
     * @throws \Spryker\Zed\Cms\Business\Exception\MissingPageException
     *
     * @return \Orm\Zed\Cms\Persistence\SpyCmsPage
     */
    protected function getCmsPage(int $idCmsPage): SpyCmsPage
    {
        $cmsPageCollection = $this->queryContainer
            ->queryCmsPageWithAllRelationsByIdPage($idCmsPage)
            ->find();

        if ($cmsPageCollection->count() === 0) {
            throw new MissingPageException(
                sprintf(
                    'There is no valid Cms page with this id: %d .',
                    $idCmsPage,
                ),
            );
        }

        return $cmsPageCollection->getFirst();
    }
}
