<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Cms\Business\Version;

use Spryker\Zed\Cms\Business\Exception\MissingPageException;
use Spryker\Zed\Cms\Persistence\CmsQueryContainerInterface;

class VersionRollback implements VersionRollbackInterface
{

    /**
     * @var \Spryker\Zed\Cms\Business\Version\VersionPublisherInterface
     */
    protected $versionPublisher;

    /**
     * @var \Spryker\Zed\Cms\Business\Version\VersionGeneratorInterface
     */
    protected $versionGenerator;

    /**
     * @var \Spryker\Zed\Cms\Business\Version\VersionMigrationInterface
     */
    protected $versionMigration;

    /**
     * @var \Spryker\Zed\Cms\Persistence\CmsQueryContainerInterface
     */
    protected $queryContainer;

    /**
     * @param \Spryker\Zed\Cms\Business\Version\VersionPublisherInterface $versionPublisher
     * @param \Spryker\Zed\Cms\Business\Version\VersionGeneratorInterface $versionGenerator
     * @param \Spryker\Zed\Cms\Business\Version\VersionMigrationInterface $versionMigration
     * @param \Spryker\Zed\Cms\Persistence\CmsQueryContainerInterface $queryContainer
     */
    public function __construct(
        VersionPublisherInterface $versionPublisher,
        VersionGeneratorInterface $versionGenerator,
        VersionMigrationInterface $versionMigration,
        CmsQueryContainerInterface $queryContainer
    ) {

        $this->versionPublisher = $versionPublisher;
        $this->versionGenerator = $versionGenerator;
        $this->versionMigration = $versionMigration;
        $this->queryContainer = $queryContainer;
    }

    /**
     * @param int $idCmsPage
     * @param int $version
     *
     * @throws \Spryker\Zed\Cms\Business\Exception\MissingPageException
     *
     * @return \Generated\Shared\Transfer\CmsVersionTransfer|null
     */
    public function rollback($idCmsPage, $version)
    {
        $originVersionEntity = $this->queryContainer->queryCmsVersionByIdPage($idCmsPage)->findOne();
        $targetVersionEntity = $this->queryContainer->queryCmsVersionByIdPageAndVersion($idCmsPage, $version)->findOne();

        if ($originVersionEntity === null || $targetVersionEntity === null) {
            throw new MissingPageException(
                sprintf(
                    "There is no valid Cms page with this id: %d or Cms version with this version: %d for rollback",
                    $idCmsPage,
                    $version
                )
            );
        }

        if (!$this->versionMigration->migrate($originVersionEntity->getData(), $targetVersionEntity->getData())) {
            return null;
        }

        $newVersion = $this->versionGenerator->generateNewCmsVersion($idCmsPage);
        $referenceVersion = sprintf(
            '%s (%s)',
            $this->versionGenerator->generateNewCmsVersionName($newVersion),
            $this->versionGenerator->generateReferenceCmsVersionName($version)
        );

        return $this->versionPublisher->publishAndVersion($idCmsPage, $referenceVersion);
    }

    /**
     * @param int $idCmsPage
     *
     * @throws \Spryker\Zed\Cms\Business\Exception\MissingPageException
     *
     * @return bool
     */
    public function revert($idCmsPage)
    {
        $versionEntity = $this->queryContainer->queryCmsVersionByIdPage($idCmsPage)->findOne();

        if ($versionEntity === null) {
            throw new MissingPageException(
                sprintf(
                    "There is no valid Cms version with this id: %d for reverting",
                    $idCmsPage
                )
            );
        }

        $latestVersionData = $versionEntity->getData();

        if (!$this->versionMigration->migrate($latestVersionData, $latestVersionData)) {
            return false;
        }

        return true;
    }

}
