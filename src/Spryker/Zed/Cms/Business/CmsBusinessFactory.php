<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Cms\Business;

use Spryker\Zed\Cms\Business\Exporter\CmsExporterInterface;
use Spryker\Zed\Cms\Business\Exporter\CmsPageEventExporter;
use Spryker\Zed\Cms\Business\Extractor\DataExtractor;
use Spryker\Zed\Cms\Business\Extractor\DataExtractorInterface;
use Spryker\Zed\Cms\Business\Mapping\CmsGlossaryKeyGenerator;
use Spryker\Zed\Cms\Business\Mapping\CmsGlossaryKeyGeneratorInterface;
use Spryker\Zed\Cms\Business\Mapping\CmsGlossaryReader;
use Spryker\Zed\Cms\Business\Mapping\CmsGlossaryReaderInterface;
use Spryker\Zed\Cms\Business\Mapping\CmsGlossarySaver;
use Spryker\Zed\Cms\Business\Mapping\CmsGlossarySaverInterface;
use Spryker\Zed\Cms\Business\Mapping\GlossaryKeyMappingManager;
use Spryker\Zed\Cms\Business\Mapping\GlossaryKeyMappingManagerInterface;
use Spryker\Zed\Cms\Business\MessageBroker\CmsPageMessageBrokerPublisher;
use Spryker\Zed\Cms\Business\MessageBroker\CmsPageMessageBrokerPublisherInterface;
use Spryker\Zed\Cms\Business\Page\CmsPageActivator;
use Spryker\Zed\Cms\Business\Page\CmsPageActivatorInterface;
use Spryker\Zed\Cms\Business\Page\CmsPageMapper;
use Spryker\Zed\Cms\Business\Page\CmsPageMapperInterface;
use Spryker\Zed\Cms\Business\Page\CmsPageReader;
use Spryker\Zed\Cms\Business\Page\CmsPageReaderInterface;
use Spryker\Zed\Cms\Business\Page\CmsPageSaver;
use Spryker\Zed\Cms\Business\Page\CmsPageSaverInterface;
use Spryker\Zed\Cms\Business\Page\CmsPageUrlBuilder;
use Spryker\Zed\Cms\Business\Page\CmsPageUrlBuilderInterface;
use Spryker\Zed\Cms\Business\Page\LocaleCmsPageDataExpander;
use Spryker\Zed\Cms\Business\Page\LocaleCmsPageDataExpanderInterface;
use Spryker\Zed\Cms\Business\Page\PageManager;
use Spryker\Zed\Cms\Business\Page\PageManagerInterface;
use Spryker\Zed\Cms\Business\Page\PageRemover;
use Spryker\Zed\Cms\Business\Page\PageRemoverInterface;
use Spryker\Zed\Cms\Business\Page\Store\CmsPageStoreRelationReader;
use Spryker\Zed\Cms\Business\Page\Store\CmsPageStoreRelationReaderInterface;
use Spryker\Zed\Cms\Business\Page\Store\CmsPageStoreRelationWriter;
use Spryker\Zed\Cms\Business\Page\Store\CmsPageStoreRelationWriterInterface;
use Spryker\Zed\Cms\Business\Template\TemplateContentReader;
use Spryker\Zed\Cms\Business\Template\TemplateContentReaderInterface;
use Spryker\Zed\Cms\Business\Template\TemplateManager;
use Spryker\Zed\Cms\Business\Template\TemplateManagerInterface;
use Spryker\Zed\Cms\Business\Template\TemplatePlaceholderParser;
use Spryker\Zed\Cms\Business\Template\TemplatePlaceholderParserInterface;
use Spryker\Zed\Cms\Business\Template\TemplateReader;
use Spryker\Zed\Cms\Business\Template\TemplateReaderInterface;
use Spryker\Zed\Cms\Business\Version\Mapper\VersionDataMapper;
use Spryker\Zed\Cms\Business\Version\Mapper\VersionDataMapperInterface;
use Spryker\Zed\Cms\Business\Version\Migration\CmsGlossaryKeyMappingMigration;
use Spryker\Zed\Cms\Business\Version\Migration\CmsPageLocalizedAttributesMigration;
use Spryker\Zed\Cms\Business\Version\Migration\CmsTemplateMigration;
use Spryker\Zed\Cms\Business\Version\Migration\MigrationInterface;
use Spryker\Zed\Cms\Business\Version\VersionFinder;
use Spryker\Zed\Cms\Business\Version\VersionFinderInterface;
use Spryker\Zed\Cms\Business\Version\VersionGenerator;
use Spryker\Zed\Cms\Business\Version\VersionGeneratorInterface;
use Spryker\Zed\Cms\Business\Version\VersionMigration;
use Spryker\Zed\Cms\Business\Version\VersionMigrationInterface;
use Spryker\Zed\Cms\Business\Version\VersionPublisher;
use Spryker\Zed\Cms\Business\Version\VersionPublisherInterface;
use Spryker\Zed\Cms\Business\Version\VersionRollback;
use Spryker\Zed\Cms\Business\Version\VersionRollbackInterface;
use Spryker\Zed\Cms\CmsDependencyProvider;
use Spryker\Zed\Cms\Dependency\Facade\CmsToEventFacadeInterface;
use Spryker\Zed\Cms\Dependency\Facade\CmsToGlossaryFacadeInterface;
use Spryker\Zed\Cms\Dependency\Facade\CmsToLocaleFacadeInterface;
use Spryker\Zed\Cms\Dependency\Facade\CmsToMessageBrokerFacadeInterface;
use Spryker\Zed\Cms\Dependency\Facade\CmsToTouchFacadeInterface;
use Spryker\Zed\Cms\Dependency\Facade\CmsToUrlFacadeInterface;
use Spryker\Zed\Cms\Dependency\Service\CmsToUtilEncodingInterface;
use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;
use Symfony\Component\Finder\Finder;

/**
 * @method \Spryker\Zed\Cms\CmsConfig getConfig()
 * @method \Spryker\Zed\Cms\Persistence\CmsQueryContainerInterface getQueryContainer()
 * @method \Spryker\Zed\Cms\Persistence\CmsRepositoryInterface getRepository()
 * @method \Spryker\Zed\Cms\Persistence\CmsEntityManagerInterface getEntityManager()
 */
class CmsBusinessFactory extends AbstractBusinessFactory
{
    public function createPageManager(): PageManagerInterface
    {
        return new PageManager(
            $this->getQueryContainer(),
            $this->createTemplateManager(),
            $this->getGlossaryFacade(),
            $this->getTouchFacade(),
            $this->getUrlFacade(),
        );
    }

    public function createTemplateManager(): TemplateManagerInterface
    {
        return new TemplateManager(
            $this->getQueryContainer(),
            $this->getConfig(),
            $this->createFinder(),
        );
    }

    public function createGlossaryKeyMappingManager(): GlossaryKeyMappingManagerInterface
    {
        return new GlossaryKeyMappingManager(
            $this->getGlossaryFacade(),
            $this->getQueryContainer(),
            $this->createTemplateManager(),
            $this->createPageManager(),
        );
    }

    public function createFinder(): Finder
    {
        return new Finder();
    }

    protected function getGlossaryFacade(): CmsToGlossaryFacadeInterface
    {
        return $this->getProvidedDependency(CmsDependencyProvider::FACADE_GLOSSARY);
    }

    protected function getTouchFacade(): CmsToTouchFacadeInterface
    {
        return $this->getProvidedDependency(CmsDependencyProvider::FACADE_TOUCH);
    }

    protected function getUrlFacade(): CmsToUrlFacadeInterface
    {
        return $this->getProvidedDependency(CmsDependencyProvider::FACADE_URL);
    }

    protected function getLocaleFacade(): CmsToLocaleFacadeInterface
    {
        return $this->getProvidedDependency(CmsDependencyProvider::FACADE_LOCALE);
    }

    public function createPageRemover(): PageRemoverInterface
    {
        return new PageRemover(
            $this->getQueryContainer(),
            $this->getTouchFacade(),
            $this->getCmsPageBeforeDeletePlugins(),
            $this->createCmsPageMapper(),
        );
    }

    public function createCmsPageSaver(): CmsPageSaverInterface
    {
        return new CmsPageSaver(
            $this->getUrlFacade(),
            $this->getTouchFacade(),
            $this->getQueryContainer(),
            $this->createCmsUrlBuilder(),
            $this->createCmsGlossarySaver(),
            $this->createTemplateManager(),
            $this->createCmsPageStoreRelationWriter(),
        );
    }

    public function createCmsPageReader(): CmsPageReaderInterface
    {
        return new CmsPageReader($this->getQueryContainer(), $this->createCmsPageMapper(), $this->getLocaleFacade());
    }

    public function createCmsGlossaryReader(): CmsGlossaryReaderInterface
    {
        return new CmsGlossaryReader($this->getQueryContainer(), $this->getLocaleFacade(), $this->createTemplateReader());
    }

    public function createCmsGlossarySaver(): CmsGlossarySaverInterface
    {
        return new CmsGlossarySaver(
            $this->getQueryContainer(),
            $this->getGlossaryFacade(),
            $this->createCmsGlossaryKeyGenerator(),
        );
    }

    public function createVersionFinder(): VersionFinderInterface
    {
        return new VersionFinder(
            $this->getQueryContainer(),
            $this->createVersionDataMapper(),
            $this->getCmsVersionTransferExpanderPlugins(),
        );
    }

    public function createVersionPublisher(): VersionPublisherInterface
    {
        return new VersionPublisher(
            $this->createVersionGenerator(),
            $this->createVersionDataMapper(),
            $this->createVersionFinder(),
            $this->getTouchFacade(),
            $this->getCmsVersionPostSavePlugins(),
        );
    }

    public function createVersionGenerator(): VersionGeneratorInterface
    {
        return new VersionGenerator(
            $this->getQueryContainer(),
        );
    }

    public function createVersionMigration(): VersionMigrationInterface
    {
        return new VersionMigration(
            $this->getUtilEncodingService(),
            [
                $this->createCmsTemplateMigration(),
                $this->createCmsPageLocalizedAttributeMigration(),
                $this->createCmsGlossaryKeyMappingMigration(),
            ],
        );
    }

    public function createCmsTemplateMigration(): MigrationInterface
    {
        return new CmsTemplateMigration(
            $this->createTemplateManager(),
            $this->getQueryContainer(),
        );
    }

    public function createCmsPageLocalizedAttributeMigration(): MigrationInterface
    {
        return new CmsPageLocalizedAttributesMigration(
            $this->getLocaleFacade(),
            $this->getQueryContainer(),
        );
    }

    public function createCmsGlossaryKeyMappingMigration(): MigrationInterface
    {
        return new CmsGlossaryKeyMappingMigration(
            $this->createCmsGlossarySaver(),
            $this->getLocaleFacade(),
            $this->getQueryContainer(),
        );
    }

    public function createVersionRollback(): VersionRollbackInterface
    {
        return new VersionRollback(
            $this->createVersionPublisher(),
            $this->createVersionGenerator(),
            $this->createVersionMigration(),
            $this->getQueryContainer(),
        );
    }

    public function createVersionDataMapper(): VersionDataMapperInterface
    {
        return new VersionDataMapper(
            $this->getUtilEncodingService(),
            $this->createCmsPageStoreRelationReader(),
        );
    }

    /**
     * @return array<\Spryker\Zed\CmsExtension\Dependency\Plugin\CmsVersionPostSavePluginInterface>
     */
    protected function getCmsVersionPostSavePlugins(): array
    {
        return $this->getProvidedDependency(CmsDependencyProvider::PLUGINS_CMS_VERSION_POST_SAVE_PLUGINS);
    }

    /**
     * @return array<\Spryker\Zed\CmsExtension\Dependency\Plugin\CmsVersionTransferExpanderPluginInterface>
     */
    protected function getCmsVersionTransferExpanderPlugins(): array
    {
        return $this->getProvidedDependency(CmsDependencyProvider::PLUGINS_CMS_VERSION_TRANSFER_EXPANDER_PLUGINS);
    }

    /**
     * @return array<\Spryker\Zed\CmsExtension\Dependency\Plugin\CmsPageDataExpanderPluginInterface>
     */
    protected function getCmsPageDataExpanderPlugins(): array
    {
        return $this->getProvidedDependency(CmsDependencyProvider::PLUGINS_CMS_PAGE_DATA_EXPANDER);
    }

    public function getUtilEncodingService(): CmsToUtilEncodingInterface
    {
        return $this->getProvidedDependency(CmsDependencyProvider::SERVICE_UTIL_ENCODING);
    }

    public function createCmsPageActivator(): CmsPageActivatorInterface
    {
        return new CmsPageActivator(
            $this->getQueryContainer(),
            $this->getTouchFacade(),
            $this->getCmsPagePostActivatorPlugins(),
            $this->createTemplateReader(),
        );
    }

    /**
     * @return array<\Spryker\Zed\Cms\Communication\Plugin\PostCmsPageActivatorPluginInterface>
     */
    protected function getCmsPagePostActivatorPlugins(): array
    {
        return $this->getProvidedDependency(CmsDependencyProvider::PLUGINS_CMS_PAGE_POST_ACTIVATOR);
    }

    public function createCmsUrlBuilder(): CmsPageUrlBuilderInterface
    {
        return new CmsPageUrlBuilder($this->getConfig());
    }

    public function createCmsGlossaryKeyGenerator(): CmsGlossaryKeyGeneratorInterface
    {
        return new CmsGlossaryKeyGenerator($this->getGlossaryFacade());
    }

    public function createLocaleCmsPageDataExpander(): LocaleCmsPageDataExpanderInterface
    {
        return new LocaleCmsPageDataExpander($this->getCmsPageDataExpanderPlugins());
    }

    public function createDataExtractor(): DataExtractorInterface
    {
        return new DataExtractor($this->getUtilEncodingService());
    }

    public function createCmsPageMapper(): CmsPageMapperInterface
    {
        return new CmsPageMapper(
            $this->createCmsUrlBuilder(),
            $this->createCmsPageStoreRelationReader(),
        );
    }

    public function createCmsPageStoreRelationReader(): CmsPageStoreRelationReaderInterface
    {
        return new CmsPageStoreRelationReader(
            $this->getQueryContainer(),
            $this->getRepository(),
        );
    }

    public function createCmsPageStoreRelationWriter(): CmsPageStoreRelationWriterInterface
    {
        return new CmsPageStoreRelationWriter(
            $this->getEntityManager(),
            $this->createCmsPageStoreRelationReader(),
        );
    }

    public function createTemplateReader(): TemplateReaderInterface
    {
        return new TemplateReader($this->createTemplateContentReader(), $this->createTemplatePlaceholderParser());
    }

    public function createTemplateContentReader(): TemplateContentReaderInterface
    {
        return new TemplateContentReader($this->getConfig(), $this->createFinder());
    }

    public function createTemplatePlaceholderParser(): TemplatePlaceholderParserInterface
    {
        return new TemplatePlaceholderParser($this->getConfig());
    }

    /**
     * @return array<\Spryker\Zed\CmsExtension\Dependency\Plugin\CmsPageBeforeDeletePluginInterface>
     */
    public function getCmsPageBeforeDeletePlugins(): array
    {
        return $this->getProvidedDependency(CmsDependencyProvider::PLUGINS_CMS_PAGE_BEFORE_DELETE);
    }

    protected function getMessageBrokerFacade(): CmsToMessageBrokerFacadeInterface
    {
        return $this->getProvidedDependency(CmsDependencyProvider::FACADE_MESSAGE_BROKER);
    }

    public function createCmsPageMessageBrokerPublisher(): CmsPageMessageBrokerPublisherInterface
    {
        return new CmsPageMessageBrokerPublisher(
            $this->getMessageBrokerFacade(),
            $this->createCmsPageReader(),
            $this->getRepository(),
            $this->createVersionFinder(),
            $this->createDataExtractor(),
            $this->createLocaleCmsPageDataExpander(),
            $this->getConfig(),
        );
    }

    public function createCmsPageEventExporter(): CmsExporterInterface
    {
        return new CmsPageEventExporter(
            $this->getEventFacade(),
            $this->getRepository(),
            $this->getConfig(),
        );
    }

    public function getEventFacade(): CmsToEventFacadeInterface
    {
        return $this->getProvidedDependency(CmsDependencyProvider::FACADE_EVENT);
    }
}
