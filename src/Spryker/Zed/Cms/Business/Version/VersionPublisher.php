<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Cms\Business\Version;

use Generated\Shared\Transfer\CmsVersionTransfer;
use Orm\Zed\Cms\Persistence\SpyCmsVersion;
use Spryker\Shared\Cms\CmsConstants;
use Spryker\Zed\Cms\Business\Version\Mapper\VersionDataMapperInterface;
use Spryker\Zed\Cms\Dependency\Facade\CmsToTouchFacadeInterface;
use Spryker\Zed\Kernel\Persistence\EntityManager\TransactionTrait;

class VersionPublisher implements VersionPublisherInterface
{
    use TransactionTrait;

    /**
     * @var \Spryker\Zed\Cms\Business\Version\VersionGeneratorInterface
     */
    protected $versionGenerator;

    /**
     * @var \Spryker\Zed\Cms\Business\Version\Mapper\VersionDataMapperInterface
     */
    protected $versionDataMapper;

    /**
     * @var \Spryker\Zed\Cms\Business\Version\VersionFinderInterface
     */
    protected $versionFinder;

    /**
     * @var \Spryker\Zed\Cms\Dependency\Facade\CmsToTouchFacadeInterface
     */
    protected $touchFacade;

    /**
     * @var array<\Spryker\Zed\CmsExtension\Dependency\Plugin\CmsVersionPostSavePluginInterface>
     */
    protected $postSavePlugins = [];

    /**
     * @param \Spryker\Zed\Cms\Business\Version\VersionGeneratorInterface $versionGenerator
     * @param \Spryker\Zed\Cms\Business\Version\Mapper\VersionDataMapperInterface $versionDataMapper
     * @param \Spryker\Zed\Cms\Business\Version\VersionFinderInterface $versionFinder
     * @param \Spryker\Zed\Cms\Dependency\Facade\CmsToTouchFacadeInterface $touchFacade
     * @param array<\Spryker\Zed\CmsExtension\Dependency\Plugin\CmsVersionPostSavePluginInterface> $postSavePlugins
     */
    public function __construct(
        VersionGeneratorInterface $versionGenerator,
        VersionDataMapperInterface $versionDataMapper,
        VersionFinderInterface $versionFinder,
        CmsToTouchFacadeInterface $touchFacade,
        array $postSavePlugins
    ) {
        $this->versionGenerator = $versionGenerator;
        $this->versionDataMapper = $versionDataMapper;
        $this->versionFinder = $versionFinder;
        $this->touchFacade = $touchFacade;
        $this->postSavePlugins = $postSavePlugins;
    }

    public function publishWithVersion(int $idCmsPage, ?string $versionName = null): CmsVersionTransfer
    {
        $cmsVersionDataTransfer = $this->versionFinder->getCmsVersionData($idCmsPage);
        $encodedData = $this->versionDataMapper->mapToJsonData($cmsVersionDataTransfer);

        return $this->createCmsVersion($encodedData, $idCmsPage, $versionName);
    }

    protected function createCmsVersion(string $data, int $idCmsPage, ?string $versionName = null): CmsVersionTransfer
    {
        $versionNumber = $this->versionGenerator->generateNewCmsVersion($idCmsPage);

        if ($versionName === null) {
            $versionName = $this->versionGenerator->generateNewCmsVersionName($versionNumber);
        }

        return $this->saveAndTouchCmsVersion($data, $idCmsPage, $versionName, $versionNumber);
    }

    protected function saveAndTouchCmsVersion(string $data, int $idCmsPage, string $versionName, int $versionNumber): CmsVersionTransfer
    {
        return $this->getTransactionHandler()->handleTransaction(function () use ($data, $idCmsPage, $versionName, $versionNumber) {
            return $this->executeSaveAndTouchCmsVersionTransaction($data, $idCmsPage, $versionName, $versionNumber);
        });
    }

    protected function executeSaveAndTouchCmsVersionTransaction(string $data, int $idCmsPage, string $versionName, int $versionNumber): CmsVersionTransfer
    {
        $cmsVersionTransfer = $this->saveCmsVersion($idCmsPage, $data, $versionNumber, $versionName);

        foreach ($this->postSavePlugins as $userPlugin) {
            $cmsVersionTransfer = $userPlugin->postSave($cmsVersionTransfer);
        }

        $this->touchFacade->touchActive(CmsConstants::RESOURCE_TYPE_PAGE, $idCmsPage);

        return $cmsVersionTransfer;
    }

    protected function saveCmsVersion(int $idCmsPage, string $data, int $versionNumber, string $versionName): CmsVersionTransfer
    {
        $cmsVersionEntity = new SpyCmsVersion();
        $cmsVersionEntity->setFkCmsPage($idCmsPage);
        $cmsVersionEntity->setData($data);
        $cmsVersionEntity->setVersion($versionNumber);
        $cmsVersionEntity->setVersionName($versionName);
        $cmsVersionEntity->save();

        return $this->versionDataMapper->mapToCmsVersionTransfer($cmsVersionEntity);
    }
}
