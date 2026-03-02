<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Cms\Business\Version\Migration;

use Generated\Shared\Transfer\CmsVersionDataTransfer;
use Spryker\Zed\Cms\Business\Mapping\CmsGlossarySaverInterface;
use Spryker\Zed\Cms\Dependency\Facade\CmsToLocaleFacadeInterface;
use Spryker\Zed\Cms\Persistence\CmsQueryContainerInterface;
use Spryker\Zed\Kernel\Persistence\EntityManager\TransactionTrait;

class CmsGlossaryKeyMappingMigration implements MigrationInterface
{
    use TransactionTrait;

    /**
     * @var \Spryker\Zed\Cms\Business\Mapping\CmsGlossarySaverInterface
     */
    protected $cmsGlossarySaver;

    /**
     * @var \Spryker\Zed\Cms\Dependency\Facade\CmsToLocaleFacadeInterface
     */
    protected $localeFacade;

    /**
     * @var \Spryker\Zed\Cms\Persistence\CmsQueryContainerInterface
     */
    protected $queryContainer;

    public function __construct(
        CmsGlossarySaverInterface $cmsGlossarySaver,
        CmsToLocaleFacadeInterface $localeFacade,
        CmsQueryContainerInterface $queryContainer
    ) {
        $this->cmsGlossarySaver = $cmsGlossarySaver;
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
        $this->cmsGlossarySaver->deleteCmsGlossary($originVersionDataTransfer->getCmsPage()->getFkPage());
        $this->cmsGlossarySaver->saveCmsGlossary($targetVersionDataTransfer->getCmsGlossary());
    }
}
