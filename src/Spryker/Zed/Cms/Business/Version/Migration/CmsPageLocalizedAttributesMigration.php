<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Cms\Business\Version\Migration;

use Generated\Shared\Transfer\CmsVersionDataTransfer;
use Orm\Zed\Cms\Persistence\SpyCmsPageLocalizedAttributes;
use Spryker\Zed\Cms\Dependency\Facade\CmsToLocaleFacadeInterface;
use Spryker\Zed\Cms\Persistence\CmsQueryContainerInterface;
use Spryker\Zed\Kernel\Persistence\EntityManager\TransactionTrait;

class CmsPageLocalizedAttributesMigration implements MigrationInterface
{
    use TransactionTrait;

    /**
     * @var \Spryker\Zed\Cms\Dependency\Facade\CmsToLocaleFacadeInterface
     */
    protected $localeFacade;

    /**
     * @var \Spryker\Zed\Cms\Persistence\CmsQueryContainerInterface
     */
    protected $queryContainer;

    public function __construct(CmsToLocaleFacadeInterface $localeFacade, CmsQueryContainerInterface $queryContainer)
    {
        $this->localeFacade = $localeFacade;
        $this->queryContainer = $queryContainer;
    }

    public function migrate(CmsVersionDataTransfer $originVersionDataTransfer, CmsVersionDataTransfer $targetVersionDataTransfer): void
    {
        $this->getTransactionHandler()->handleTransaction(function () use ($originVersionDataTransfer, $targetVersionDataTransfer) {
            $this->executeMigrateTransaction($originVersionDataTransfer, $targetVersionDataTransfer);
        });
    }

    protected function executeMigrateTransaction(CmsVersionDataTransfer $originVersionDataTransfer, CmsVersionDataTransfer $targetVersionDataTransfer): void
    {
        $this->migratePageAttributes($originVersionDataTransfer, $targetVersionDataTransfer);
        $this->migrateMetaAttributes($originVersionDataTransfer, $targetVersionDataTransfer);
    }

    protected function migratePageAttributes(CmsVersionDataTransfer $originVersionDataTransfer, CmsVersionDataTransfer $targetVersionDataTransfer): void
    {
        foreach ($targetVersionDataTransfer->getCmsPage()->getPageAttributes() as $pageAttributesTransfer) {
            $cmsLocalizedAttributeEntity = $this->findOrCreatePageLocalizedAttribute(
                $originVersionDataTransfer->getCmsPage()->getFkPage(),
                $pageAttributesTransfer->getLocaleName(),
            );

            $cmsLocalizedAttributeEntity->setName($pageAttributesTransfer->getName());
            $cmsLocalizedAttributeEntity->save();
        }
    }

    protected function migrateMetaAttributes(CmsVersionDataTransfer $originVersionDataTransfer, CmsVersionDataTransfer $targetVersionDataTransfer): void
    {
        foreach ($targetVersionDataTransfer->getCmsPage()->getMetaAttributes() as $metaAttributesTransfer) {
            $cmsLocalizedAttributeEntity = $this->findOrCreatePageLocalizedAttribute(
                $originVersionDataTransfer->getCmsPage()->getFkPage(),
                $metaAttributesTransfer->getLocaleName(),
            );

            $cmsLocalizedAttributeEntity->setMetaTitle($metaAttributesTransfer->getMetaTitle());
            $cmsLocalizedAttributeEntity->setMetaKeywords($metaAttributesTransfer->getMetaKeywords());
            $cmsLocalizedAttributeEntity->setMetaDescription($metaAttributesTransfer->getMetaDescription());
            $cmsLocalizedAttributeEntity->save();
        }
    }

    protected function findOrCreatePageLocalizedAttribute(int $idCmsPage, string $localeName): SpyCmsPageLocalizedAttributes
    {
        $localeTransfer = $this->localeFacade->getLocale($localeName);

        return $this->queryContainer
            ->queryCmsPageLocalizedAttributesByFkPageAndFkLocale($idCmsPage, $localeTransfer->getIdLocale())
            ->findOneOrCreate();
    }
}
