<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Cms\Business\Page\Store;

use Generated\Shared\Transfer\StoreRelationTransfer;
use Spryker\Zed\Cms\Persistence\CmsEntityManagerInterface;

class CmsPageStoreRelationWriter implements CmsPageStoreRelationWriterInterface
{
    /**
     * @var \Spryker\Zed\Cms\Persistence\CmsEntityManagerInterface
     */
    protected $cmsEntityManager;

    /**
     * @var \Spryker\Zed\Cms\Business\Page\Store\CmsPageStoreRelationReaderInterface
     */
    protected $cmsPageStoreRelationReader;

    public function __construct(CmsEntityManagerInterface $cmsEntityManager, CmsPageStoreRelationReaderInterface $cmsPageStoreRelationReader)
    {
        $this->cmsEntityManager = $cmsEntityManager;
        $this->cmsPageStoreRelationReader = $cmsPageStoreRelationReader;
    }

    public function update(StoreRelationTransfer $storeRelationTransfer): void
    {
        $storeRelationTransfer->requireIdEntity();

        $currentIdStores = $this->getIdStoresByIdCmsPage($storeRelationTransfer->getIdEntity());
        $requestedIdStores = $storeRelationTransfer->getIdStores();

        $saveIdStores = array_diff($requestedIdStores, $currentIdStores);
        $deleteIdStores = array_diff($currentIdStores, $requestedIdStores);

        $this->cmsEntityManager->addStoreRelations($saveIdStores, $storeRelationTransfer->getIdEntity());
        $this->cmsEntityManager->removeStoreRelations($deleteIdStores, $storeRelationTransfer->getIdEntity());
    }

    /**
     * @param int $idCmsPage
     *
     * @return array<int>
     */
    protected function getIdStoresByIdCmsPage(int $idCmsPage): array
    {
        $storeRelation = $this->cmsPageStoreRelationReader->getStoreRelation(
            (new StoreRelationTransfer())->setIdEntity($idCmsPage),
        );

        return $storeRelation->getIdStores();
    }
}
