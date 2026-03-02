<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Cms\Business\Mapping;

use Generated\Shared\Transfer\LocaleTransfer;
use Generated\Shared\Transfer\PageKeyMappingTransfer;
use Generated\Shared\Transfer\PageTransfer;
use Orm\Zed\Cms\Persistence\Map\SpyCmsGlossaryKeyMappingTableMap;
use Orm\Zed\Cms\Persistence\SpyCmsGlossaryKeyMapping;
use Spryker\Zed\Cms\Business\Exception\MappingAmbiguousException;
use Spryker\Zed\Cms\Business\Exception\MissingGlossaryKeyMappingException;
use Spryker\Zed\Cms\Business\Page\PageManagerInterface;
use Spryker\Zed\Cms\Business\Template\TemplateManagerInterface;
use Spryker\Zed\Cms\Dependency\Facade\CmsToGlossaryFacadeInterface;
use Spryker\Zed\Cms\Persistence\CmsQueryContainerInterface;
use Spryker\Zed\Kernel\Persistence\EntityManager\TransactionTrait;

class GlossaryKeyMappingManager implements GlossaryKeyMappingManagerInterface
{
    use TransactionTrait;

    /**
     * @var string
     */
    public const GENERATED_GLOSSARY_KEY_PREFIX = 'generated.cms';

    /**
     * @var \Spryker\Zed\Cms\Dependency\Facade\CmsToGlossaryFacadeInterface
     */
    protected $glossaryFacade;

    /**
     * @var \Spryker\Zed\Cms\Persistence\CmsQueryContainerInterface
     */
    protected $cmsQueryContainer;

    /**
     * @var \Spryker\Zed\Cms\Business\Template\TemplateManagerInterface
     */
    protected $templateManager;

    /**
     * @var \Spryker\Zed\Cms\Business\Page\PageManagerInterface
     */
    protected $pageManager;

    public function __construct(
        CmsToGlossaryFacadeInterface $glossaryFacade,
        CmsQueryContainerInterface $cmsQueryContainer,
        TemplateManagerInterface $templateManager,
        PageManagerInterface $pageManager
    ) {
        $this->glossaryFacade = $glossaryFacade;
        $this->cmsQueryContainer = $cmsQueryContainer;
        $this->templateManager = $templateManager;
        $this->pageManager = $pageManager;
    }

    /**
     * @param int $idPage
     * @param string $placeholder
     * @param array<string, mixed> $data
     *
     * @return string
     */
    public function translatePlaceholder(int $idPage, string $placeholder, array $data = []): string
    {
        $glossaryKeyMapping = $this->getPagePlaceholderMapping($idPage, $placeholder);

        return $this->glossaryFacade->translateByKeyId($glossaryKeyMapping->getFkGlossaryKey(), $data);
    }

    /**
     * @param int $idPage
     * @param string $placeholder
     *
     * @throws \Spryker\Zed\Cms\Business\Exception\MissingGlossaryKeyMappingException
     *
     * @return \Generated\Shared\Transfer\PageKeyMappingTransfer
     */
    public function getPagePlaceholderMapping(int $idPage, string $placeholder): PageKeyMappingTransfer
    {
        $glossaryKeyMappingEntity = $this->cmsQueryContainer->queryGlossaryKeyMapping($idPage, $placeholder)
            ->findOne();

        if (!$glossaryKeyMappingEntity) {
            throw new MissingGlossaryKeyMappingException(sprintf('Tried to translate a missing placeholder mapping: Placeholder %s on Page Id %s', $placeholder, $idPage));
        }

        return $this->convertMappingEntityToTransfer($glossaryKeyMappingEntity);
    }

    public function savePageKeyMapping(PageKeyMappingTransfer $pageKeyMappingTransfer): PageKeyMappingTransfer
    {
        if ($pageKeyMappingTransfer->getIdCmsGlossaryKeyMapping() === null) {
            return $this->createPageKeyMapping($pageKeyMappingTransfer);
        } else {
            return $this->updatePageKeyMapping($pageKeyMappingTransfer);
        }
    }

    public function savePageKeyMappingAndTouch(PageKeyMappingTransfer $pageKeyMappingTransfer, ?LocaleTransfer $localeTransfer = null): PageKeyMappingTransfer
    {
        $savedPageKeyMappingTransfer = $this->savePageKeyMapping($pageKeyMappingTransfer);

        $pageTransfer = (new PageTransfer())->setIdCmsPage($savedPageKeyMappingTransfer->getFkPage());
        $this->pageManager->touchPageActive($pageTransfer, $localeTransfer);

        return $savedPageKeyMappingTransfer;
    }

    protected function createPageKeyMapping(PageKeyMappingTransfer $pageKeyMapping): PageKeyMappingTransfer
    {
        $this->checkPagePlaceholderNotAmbiguous($pageKeyMapping->getFkPage(), $pageKeyMapping->getPlaceholder());

        $mappingEntity = new SpyCmsGlossaryKeyMapping();
        $mappingEntity->fromArray($pageKeyMapping->toArray());

        $mappingEntity->save();
        $pageKeyMapping->setIdCmsGlossaryKeyMapping($mappingEntity->getPrimaryKey());

        return $pageKeyMapping;
    }

    protected function updatePageKeyMapping(PageKeyMappingTransfer $pageKeyMapping): PageKeyMappingTransfer
    {
        $mappingEntity = $this->getGlossaryKeyMappingById($pageKeyMapping->getIdCmsGlossaryKeyMapping());
        $mappingEntity->fromArray($pageKeyMapping->toArray());

        if (!$mappingEntity->isModified()) {
            return $pageKeyMapping;
        }

        $isPlaceholderModified = $mappingEntity->isColumnModified(SpyCmsGlossaryKeyMappingTableMap::COL_PLACEHOLDER);
        $isPageIdModified = $mappingEntity->isColumnModified(SpyCmsGlossaryKeyMappingTableMap::COL_FK_PAGE);

        if ($isPlaceholderModified || $isPageIdModified) {
            $this->checkPagePlaceholderNotAmbiguous($pageKeyMapping->getFkPage(), $pageKeyMapping->getPlaceholder());
        }

        $mappingEntity->save();

        return $pageKeyMapping;
    }

    /**
     * @param int $idPage
     * @param string $placeholder
     *
     * @throws \Spryker\Zed\Cms\Business\Exception\MappingAmbiguousException
     *
     * @return void
     */
    protected function checkPagePlaceholderNotAmbiguous(int $idPage, string $placeholder): void
    {
        if ($this->hasPagePlaceholderMapping($idPage, $placeholder)) {
            throw new MappingAmbiguousException(sprintf('Tried to create an ambiguous mapping for placeholder %s on page %s', $placeholder, $idPage));
        }
    }

    public function hasPagePlaceholderMapping(int $idPage, string $placeholder): bool
    {
        $mappingCount = $this->cmsQueryContainer->queryGlossaryKeyMapping($idPage, $placeholder)
            ->count();

        return $mappingCount > 0;
    }

    /**
     * @param int $idMapping
     *
     * @throws \Spryker\Zed\Cms\Business\Exception\MissingGlossaryKeyMappingException
     *
     * @return \Orm\Zed\Cms\Persistence\SpyCmsGlossaryKeyMapping
     */
    protected function getGlossaryKeyMappingById(int $idMapping): SpyCmsGlossaryKeyMapping
    {
        $mappingEntity = $this->cmsQueryContainer->queryGlossaryKeyMappingById($idMapping)
            ->findOne();
        if (!$mappingEntity) {
            throw new MissingGlossaryKeyMappingException(sprintf('Tried to retrieve a missing glossary key mapping with id %s', $idMapping));
        }

        return $mappingEntity;
    }

    public function addPlaceholderText(
        PageTransfer $pageTransfer,
        string $placeholder,
        string $value,
        ?LocaleTransfer $localeTransfer = null,
        bool $autoGlossaryKeyIncrement = true
    ): PageKeyMappingTransfer {
        $template = $this->templateManager->getTemplateById($pageTransfer->getFkTemplate());

        $uniquePlaceholder = $placeholder . '-' . $pageTransfer->getIdCmsPage();
        $keyName = $this->generateGlossaryKeyName($template->getTemplateName(), $uniquePlaceholder, $autoGlossaryKeyIncrement);

        return $this->getTransactionHandler()->handleTransaction(function () use ($pageTransfer, $placeholder, $keyName, $value, $localeTransfer): PageKeyMappingTransfer {
            return $this->executeAddPlaceholderTextTransaction($pageTransfer, $placeholder, $keyName, $value, $localeTransfer);
        });
    }

    public function generateGlossaryKeyName(string $templateName, string $placeholder, bool $autoIncrement = true): string
    {
        $keyName = static::GENERATED_GLOSSARY_KEY_PREFIX . '.';
        $keyName .= str_replace([' ', '.'], '-', $templateName) . '.';
        $keyName .= str_replace([' ', '.'], '-', $placeholder);

        $index = 0;

        $candidate = $keyName . $index;

        while ($this->glossaryFacade->hasKey($candidate) && $autoIncrement === true) {
            $candidate = $keyName . ++$index;
        }

        return $candidate;
    }

    public function deletePageKeyMapping(PageTransfer $pageTransfer, string $placeholder): bool
    {
        $mappingQuery = $this->cmsQueryContainer->queryGlossaryKeyMapping($pageTransfer->getIdCmsPage(), $placeholder);
        $mappingQuery->delete();

        return true;
    }

    public function deleteGlossaryKeysByIdPage(int $idPage): bool
    {
        $mappedGlossaries = $this->cmsQueryContainer->queryGlossaryKeyMappingsByPageId($idPage)
            ->find();

        $pageTransfer = (new PageTransfer())->setIdCmsPage($idPage);

        foreach ($mappedGlossaries->getData() as $glossaryMapping) {
            $this->deletePageKeyMapping($pageTransfer, $glossaryMapping->getPlaceholder());
        }

        return true;
    }

    protected function executeAddPlaceholderTextTransaction(
        PageTransfer $pageTransfer,
        string $placeholder,
        string $glossaryKey,
        string $value,
        ?LocaleTransfer $localeTransfer = null
    ): PageKeyMappingTransfer {
        $pageKeyMapping = $this->createGlossaryPageKeyMapping($pageTransfer, $placeholder, $glossaryKey, $value, $localeTransfer);

        if ($this->hasPagePlaceholderMapping($pageTransfer->getIdCmsPage(), $placeholder)) {
            return $pageKeyMapping;
        }

        return $this->savePageKeyMapping($pageKeyMapping);
    }

    protected function convertMappingEntityToTransfer(SpyCmsGlossaryKeyMapping $mappingEntity): PageKeyMappingTransfer
    {
        $mappingTransfer = new PageKeyMappingTransfer();
        $mappingTransfer->fromArray($mappingEntity->toArray(), true);

        return $mappingTransfer;
    }

    protected function createGlossaryTranslation(string $keyName, string $value, ?LocaleTransfer $localeTransfer = null): void
    {
        if ($localeTransfer !== null) {
            $this->glossaryFacade->createAndTouchTranslation($keyName, $localeTransfer, $value);
        } else {
            $this->glossaryFacade->createTranslationForCurrentLocale($keyName, $value);
        }
    }

    protected function createPageKeyMappingTransfer(PageTransfer $page, string $placeholder, int $idKey): PageKeyMappingTransfer
    {
        $pageKeyMapping = new PageKeyMappingTransfer();
        $pageKeyMapping->setFkGlossaryKey($idKey);
        $pageKeyMapping->setPlaceholder($placeholder);
        $pageKeyMapping->setFkPage($page->getIdCmsPage());

        return $pageKeyMapping;
    }

    protected function createGlossaryPageKeyMapping(
        PageTransfer $page,
        string $placeholder,
        string $keyName,
        string $value,
        ?LocaleTransfer $localeTransfer = null
    ): PageKeyMappingTransfer {
        $idKey = $this->glossaryFacade->getOrCreateKey($keyName);
        $this->createGlossaryTranslation($keyName, $value, $localeTransfer);
        $pageKeyMapping = $this->createPageKeyMappingTransfer($page, $placeholder, $idKey);

        return $pageKeyMapping;
    }
}
