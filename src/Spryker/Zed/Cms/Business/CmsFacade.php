<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Cms\Business;

use Generated\Shared\Transfer\CmsGlossaryTransfer;
use Generated\Shared\Transfer\CmsPageAttributesTransfer;
use Generated\Shared\Transfer\CmsPageTransfer;
use Generated\Shared\Transfer\CmsTemplateTransfer;
use Generated\Shared\Transfer\CmsVersionDataTransfer;
use Generated\Shared\Transfer\CmsVersionTransfer;
use Generated\Shared\Transfer\LocaleCmsPageDataTransfer;
use Generated\Shared\Transfer\LocaleTransfer;
use Generated\Shared\Transfer\PageKeyMappingTransfer;
use Generated\Shared\Transfer\PageTransfer;
use Generated\Shared\Transfer\UrlTransfer;
use Spryker\Zed\Kernel\Business\AbstractFacade;

/**
 * @method \Spryker\Zed\Cms\Business\CmsBusinessFactory getFactory()
 * @method \Spryker\Zed\Cms\Persistence\CmsRepositoryInterface getRepository()
 * @method \Spryker\Zed\Cms\Persistence\CmsEntityManagerInterface getEntityManager()
 */
class CmsFacade extends AbstractFacade implements CmsFacadeInterface
{
    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param string $name
     * @param string $path
     *
     * @return \Generated\Shared\Transfer\CmsTemplateTransfer
     */
    public function createTemplate(string $name, string $path): CmsTemplateTransfer
    {
        $templateManager = $this->getFactory()->createTemplateManager();

        return $templateManager->createTemplate($name, $path);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param string $path
     *
     * @return \Generated\Shared\Transfer\CmsTemplateTransfer
     */
    public function getTemplate(string $path): CmsTemplateTransfer
    {
        $templateManager = $this->getFactory()->createTemplateManager();

        return $templateManager->getTemplateByPath($path);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param string $path
     *
     * @return bool
     */
    public function hasTemplate(string $path): bool
    {
        $templateManager = $this->getFactory()->createTemplateManager();

        return $templateManager->hasTemplatePath($path);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @deprecated Use {@link createPage()} or {@link updatePage()} instead.
     *
     * @param \Generated\Shared\Transfer\PageTransfer $pageTransfer
     *
     * @return \Generated\Shared\Transfer\PageTransfer
     */
    public function savePage(PageTransfer $pageTransfer): PageTransfer
    {
        $pageManager = $this->getFactory()->createPageManager();

        return $pageManager->savePage($pageTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\PageKeyMappingTransfer $pageKeyMappingTransfer
     *
     * @return \Generated\Shared\Transfer\PageKeyMappingTransfer
     */
    public function savePageKeyMapping(PageKeyMappingTransfer $pageKeyMappingTransfer): PageKeyMappingTransfer
    {
        $glossaryKeyMappingManager = $this->getFactory()->createGlossaryKeyMappingManager();

        return $glossaryKeyMappingManager->savePageKeyMapping($pageKeyMappingTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\PageKeyMappingTransfer $pageKeyMappingTransfer
     * @param \Generated\Shared\Transfer\LocaleTransfer|null $localeTransfer
     *
     * @return \Generated\Shared\Transfer\PageKeyMappingTransfer
     */
    public function savePageKeyMappingAndTouch(PageKeyMappingTransfer $pageKeyMappingTransfer, ?LocaleTransfer $localeTransfer = null): PageKeyMappingTransfer
    {
        $glossaryKeyMappingManager = $this->getFactory()->createGlossaryKeyMappingManager();

        return $glossaryKeyMappingManager->savePageKeyMappingAndTouch($pageKeyMappingTransfer, $localeTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param int $idPage
     * @param string $placeholder
     *
     * @return bool
     */
    public function hasPagePlaceholderMapping(int $idPage, string $placeholder): bool
    {
        $glossaryKeyMappingManager = $this->getFactory()->createGlossaryKeyMappingManager();

        return $glossaryKeyMappingManager->hasPagePlaceholderMapping($idPage, $placeholder);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param int $idPage
     * @param string $placeholder
     *
     * @return \Generated\Shared\Transfer\PageKeyMappingTransfer
     */
    public function getPagePlaceholderMapping(int $idPage, string $placeholder): PageKeyMappingTransfer
    {
        $glossaryKeyMappingManager = $this->getFactory()->createGlossaryKeyMappingManager();

        return $glossaryKeyMappingManager->getPagePlaceholderMapping($idPage, $placeholder);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\CmsTemplateTransfer $cmsTemplateTransfer
     *
     * @return \Generated\Shared\Transfer\CmsTemplateTransfer
     */
    public function saveTemplate(CmsTemplateTransfer $cmsTemplateTransfer): CmsTemplateTransfer
    {
        $templateManager = $this->getFactory()->createTemplateManager();

        return $templateManager->saveTemplate($cmsTemplateTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param int $idPage
     * @param string $placeholder
     * @param array<string, mixed> $data
     *
     * @return string
     */
    public function translatePlaceholder(int $idPage, string $placeholder, array $data = []): string
    {
        $glossaryKeyMappingManager = $this->getFactory()->createGlossaryKeyMappingManager();

        return $glossaryKeyMappingManager->translatePlaceholder($idPage, $placeholder, $data);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\PageTransfer $pageTransfer
     * @param string $placeholder
     * @param string $value
     * @param \Generated\Shared\Transfer\LocaleTransfer|null $localeTransfer
     * @param bool $autoGlossaryKeyIncrement
     *
     * @return \Generated\Shared\Transfer\PageKeyMappingTransfer
     */
    public function addPlaceholderText(
        PageTransfer $pageTransfer,
        string $placeholder,
        string $value,
        ?LocaleTransfer $localeTransfer = null,
        bool $autoGlossaryKeyIncrement = true
    ): PageKeyMappingTransfer {
        $glossaryKeyMappingManager = $this->getFactory()->createGlossaryKeyMappingManager();

        return $glossaryKeyMappingManager->addPlaceholderText($pageTransfer, $placeholder, $value, $localeTransfer, $autoGlossaryKeyIncrement);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\PageTransfer $pageTransfer
     * @param string $placeholder
     *
     * @return bool
     */
    public function deletePageKeyMapping(PageTransfer $pageTransfer, string $placeholder): bool
    {
        $glossaryKeyMappingManager = $this->getFactory()->createGlossaryKeyMappingManager();

        return $glossaryKeyMappingManager->deletePageKeyMapping($pageTransfer, $placeholder);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\PageTransfer $pageTransfer
     * @param \Generated\Shared\Transfer\LocaleTransfer|null $localeTransfer
     *
     * @return void
     */
    public function touchPageActive(PageTransfer $pageTransfer, ?LocaleTransfer $localeTransfer = null): void
    {
        $pageManager = $this->getFactory()->createPageManager();
        $pageManager->touchPageActive($pageTransfer, $localeTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\PageTransfer $pageTransfer
     *
     * @return \Generated\Shared\Transfer\UrlTransfer
     */
    public function savePageUrlAndTouch(PageTransfer $pageTransfer): UrlTransfer
    {
        $pageManager = $this->getFactory()->createPageManager();

        return $pageManager->savePageUrlAndTouch($pageTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param int $idPage
     *
     * @return bool
     */
    public function deleteGlossaryKeysByIdPage(int $idPage): bool
    {
        $glossaryKeyMappingManager = $this->getFactory()->createGlossaryKeyMappingManager();

        return $glossaryKeyMappingManager->deleteGlossaryKeysByIdPage($idPage);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param string $cmsTemplateFolderPath
     *
     * @return bool
     */
    public function syncTemplate(string $cmsTemplateFolderPath): bool
    {
        $templateManager = $this->getFactory()->createTemplateManager();

        return $templateManager->syncTemplate($cmsTemplateFolderPath);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param string $templateName
     * @param string $placeholder
     *
     * @return string
     */
    public function generateGlossaryKeyName(string $templateName, string $placeholder): string
    {
        $glossaryKeyMappingManager = $this->getFactory()->createGlossaryKeyMappingManager();

        return $glossaryKeyMappingManager->generateGlossaryKeyName($templateName, $placeholder);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param int $idCmsPage
     *
     * @return void
     */
    public function deletePageById(int $idCmsPage): void
    {
        $this->getFactory()
            ->createPageRemover()
            ->delete($idCmsPage);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\CmsGlossaryTransfer $cmsGlossaryTransfer
     *
     * @return \Generated\Shared\Transfer\CmsGlossaryTransfer
     */
    public function saveCmsGlossary(CmsGlossaryTransfer $cmsGlossaryTransfer): CmsGlossaryTransfer
    {
        return $this->getFactory()
            ->createCmsGlossarySaver()
            ->saveCmsGlossary($cmsGlossaryTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\CmsPageTransfer $cmsPageTransfer
     *
     * @return int
     */
    public function createPage(CmsPageTransfer $cmsPageTransfer): int
    {
        return $this->getFactory()
            ->createCmsPageSaver()
            ->createPage($cmsPageTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\PageTransfer $pageTransfer
     * @param string $url
     * @param \Generated\Shared\Transfer\LocaleTransfer $localeTransfer
     *
     * @return \Generated\Shared\Transfer\UrlTransfer
     */
    public function createPageUrlWithLocale(PageTransfer $pageTransfer, string $url, LocaleTransfer $localeTransfer): UrlTransfer
    {
        return $this->getFactory()
            ->createPageManager()
            ->createPageUrlWithLocale($pageTransfer, $url, $localeTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param int $idCmsPage
     *
     * @return \Generated\Shared\Transfer\CmsPageTransfer|null
     */
    public function findCmsPageById(int $idCmsPage): ?CmsPageTransfer
    {
        return $this->getFactory()
            ->createCmsPageReader()
            ->findCmsPageById($idCmsPage);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param int $idCmsPage
     *
     * @return \Generated\Shared\Transfer\CmsGlossaryTransfer|null
     */
    public function findPageGlossaryAttributes(int $idCmsPage): ?CmsGlossaryTransfer
    {
        return $this->getFactory()
            ->createCmsGlossaryReader()
            ->findPageGlossaryAttributes($idCmsPage);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\CmsPageTransfer $cmsPageTransfer
     *
     * @return \Generated\Shared\Transfer\CmsPageTransfer
     */
    public function updatePage(CmsPageTransfer $cmsPageTransfer): CmsPageTransfer
    {
        return $this->getFactory()
            ->createCmsPageSaver()
            ->updatePage($cmsPageTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param int $idCmsPage
     *
     * @return void
     */
    public function activatePage(int $idCmsPage): void
    {
        $this->getFactory()
            ->createCmsPageActivator()
            ->activate($idCmsPage);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param int $idCmsPage
     *
     * @return void
     */
    public function deactivatePage(int $idCmsPage): void
    {
        $this->getFactory()
            ->createCmsPageActivator()
            ->deactivate($idCmsPage);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\CmsPageAttributesTransfer $cmsPageAttributesTransfer
     *
     * @return string
     */
    public function getPageUrlPrefix(CmsPageAttributesTransfer $cmsPageAttributesTransfer): string
    {
        return $this->getFactory()
            ->createCmsUrlBuilder()
            ->getPageUrlPrefix($cmsPageAttributesTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\CmsPageAttributesTransfer $cmsPageAttributesTransfer
     *
     * @return string
     */
    public function buildPageUrl(CmsPageAttributesTransfer $cmsPageAttributesTransfer): string
    {
        return $this->getFactory()
            ->createCmsUrlBuilder()
            ->buildPageUrl($cmsPageAttributesTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param int $idCmsPage
     * @param string|null $versionName
     *
     * @throws \Spryker\Zed\Cms\Business\Exception\MissingPageException
     *
     * @return \Generated\Shared\Transfer\CmsVersionTransfer
     */
    public function publishWithVersion(int $idCmsPage, ?string $versionName = null): CmsVersionTransfer
    {
        return $this->getFactory()
            ->createVersionPublisher()
            ->publishWithVersion($idCmsPage, $versionName);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param int $idCmsPage
     *
     * @return \Generated\Shared\Transfer\CmsVersionDataTransfer
     */
    public function getCmsVersionData(int $idCmsPage): CmsVersionDataTransfer
    {
        return $this->getFactory()
            ->createVersionFinder()
            ->getCmsVersionData($idCmsPage);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param string $cmsPageData
     *
     * @return \Generated\Shared\Transfer\CmsVersionDataTransfer
     */
    public function extractCmsVersionDataTransfer(string $cmsPageData): CmsVersionDataTransfer
    {
        return $this->getFactory()
            ->createDataExtractor()
            ->extractCmsVersionDataTransfer($cmsPageData);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\CmsVersionDataTransfer $cmsVersionDataTransfer
     * @param \Generated\Shared\Transfer\LocaleTransfer $localeTransfer
     *
     * @return \Generated\Shared\Transfer\LocaleCmsPageDataTransfer
     */
    public function extractLocaleCmsPageDataTransfer(CmsVersionDataTransfer $cmsVersionDataTransfer, LocaleTransfer $localeTransfer): LocaleCmsPageDataTransfer
    {
        $localeCmsPageDataTransfer = $this->getFactory()
            ->createDataExtractor()
            ->extractLocaleCmsPageDataTransfer($cmsVersionDataTransfer, $localeTransfer);

        return $localeCmsPageDataTransfer;
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\LocaleCmsPageDataTransfer $localeCmsPageDataTransfer
     * @param \Generated\Shared\Transfer\LocaleTransfer $localeTransfer
     *
     * @return array
     */
    public function calculateFlattenedLocaleCmsPageData(LocaleCmsPageDataTransfer $localeCmsPageDataTransfer, LocaleTransfer $localeTransfer): array
    {
        return $this->getFactory()
            ->createLocaleCmsPageDataExpander()
            ->calculateFlattenedLocaleCmsPageData($localeCmsPageDataTransfer, $localeTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param int $idCmsPage
     * @param int $version
     *
     * @throws \Spryker\Zed\Cms\Business\Exception\MissingPageException
     *
     * @return \Generated\Shared\Transfer\CmsVersionTransfer
     */
    public function rollback(int $idCmsPage, int $version): CmsVersionTransfer
    {
        return $this->getFactory()
            ->createVersionRollback()
            ->rollback($idCmsPage, $version);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param int $idCmsPage
     *
     * @return void
     */
    public function revert(int $idCmsPage): void
    {
        $this->getFactory()
            ->createVersionRollback()
            ->revert($idCmsPage);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param int $idCmsPage
     *
     * @return \Generated\Shared\Transfer\CmsVersionTransfer|null
     */
    public function findLatestCmsVersionByIdCmsPage(int $idCmsPage): ?CmsVersionTransfer
    {
        return $this->getFactory()
            ->createVersionFinder()
            ->findLatestCmsVersionByIdCmsPage($idCmsPage);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param int $idCmsPage
     *
     * @return array<\Generated\Shared\Transfer\CmsVersionTransfer>
     */
    public function findAllCmsVersionByIdCmsPage(int $idCmsPage): array
    {
        return $this->getFactory()
            ->createVersionFinder()
            ->findAllCmsVersionByIdCmsPage($idCmsPage);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param int $idCmsPage
     * @param int $version
     *
     * @return \Generated\Shared\Transfer\CmsVersionTransfer|null
     */
    public function findCmsVersionByIdCmsPageAndVersion(int $idCmsPage, int $version): ?CmsVersionTransfer
    {
        return $this->getFactory()
            ->createVersionFinder()
            ->findCmsVersionByIdCmsPageAndVersion($idCmsPage, $version);
    }
}
