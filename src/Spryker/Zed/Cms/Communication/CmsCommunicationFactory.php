<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Cms\Communication;

use Spryker\Zed\Cms\CmsDependencyProvider;
use Spryker\Zed\Cms\Communication\Form\CmsGlossaryForm;
use Spryker\Zed\Cms\Communication\Form\CmsRedirectForm;
use Spryker\Zed\Cms\Communication\Form\DataProvider\CmsGlossaryFormDataProvider;
use Spryker\Zed\Cms\Communication\Form\DataProvider\CmsRedirectFormDataProvider;
use Spryker\Zed\Cms\Communication\Form\DeleteCmsRedirectForm;
use Spryker\Zed\Cms\Communication\Table\CmsGlossaryTable;
use Spryker\Zed\Cms\Communication\Table\CmsRedirectTable;
use Spryker\Zed\Cms\Dependency\Facade\CmsToGlossaryFacadeInterface;
use Spryker\Zed\Cms\Dependency\Facade\CmsToLocaleFacadeInterface;
use Spryker\Zed\Cms\Dependency\Facade\CmsToUrlFacadeInterface;
use Spryker\Zed\Kernel\Communication\AbstractCommunicationFactory;
use Symfony\Component\Form\FormInterface;

/**
 * @method \Spryker\Zed\Cms\Persistence\CmsQueryContainerInterface getQueryContainer()
 * @method \Spryker\Zed\Cms\CmsConfig getConfig()
 * @method \Spryker\Zed\Cms\Business\CmsFacadeInterface getFacade()
 * @method \Spryker\Zed\Cms\Persistence\CmsRepositoryInterface getRepository()
 * @method \Spryker\Zed\Cms\Persistence\CmsEntityManagerInterface getEntityManager()
 */
class CmsCommunicationFactory extends AbstractCommunicationFactory
{
    public function createCmsRedirectTable(): CmsRedirectTable
    {
        $urlQuery = $this->getQueryContainer()
            ->queryUrlsWithRedirect();

        return new CmsRedirectTable($urlQuery);
    }

    public function createCmsGlossaryTable(int $idPage, int $fkLocale, array $placeholders = [], array $searchArray = []): CmsGlossaryTable
    {
        $glossaryQuery = $this->getQueryContainer()
            ->queryGlossaryKeyMappingsWithKeyByPageId($idPage, $fkLocale);

        return new CmsGlossaryTable($glossaryQuery, $idPage, $placeholders, $searchArray);
    }

    public function getCmsRedirectForm(array $formData = [], array $formOptions = []): FormInterface
    {
        return $this->getFormFactory()->create(CmsRedirectForm::class, $formData, $formOptions);
    }

    public function createCmsRedirectFormDataProvider(): CmsRedirectFormDataProvider
    {
        return new CmsRedirectFormDataProvider($this->getQueryContainer());
    }

    public function getCmsGlossaryForm(array $formData = [], array $formOptions = []): FormInterface
    {
        return $this->getFormFactory()->create(CmsGlossaryForm::class, $formData, $formOptions);
    }

    public function createCmsGlossaryFormDataProvider(): CmsGlossaryFormDataProvider
    {
        return new CmsGlossaryFormDataProvider($this->getQueryContainer());
    }

    public function createDeleteCmsRedirectForm(): FormInterface
    {
        return $this->getFormFactory()->create(DeleteCmsRedirectForm::class, [], [
            'fields' => [],
        ]);
    }

    /**
     * @deprecated Use {@link getTemplateRealPaths()} instead.
     *
     * @param string $templateRelativePath
     *
     * @return string
     */
    public function getTemplateRealPath($templateRelativePath)
    {
        return $this->getConfig()
            ->getTemplateRealPath($templateRelativePath);
    }

    public function getTemplateRealPaths(string $templateRelativePath): array
    {
        return $this->getConfig()
            ->getTemplateRealPaths($templateRelativePath);
    }

    public function getLocaleFacade(): CmsToLocaleFacadeInterface
    {
        return $this->getProvidedDependency(CmsDependencyProvider::FACADE_LOCALE);
    }

    public function getUrlFacade(): CmsToUrlFacadeInterface
    {
        return $this->getProvidedDependency(CmsDependencyProvider::FACADE_URL);
    }

    public function getGlossaryFacade(): CmsToGlossaryFacadeInterface
    {
        return $this->getProvidedDependency(CmsDependencyProvider::FACADE_GLOSSARY);
    }
}
