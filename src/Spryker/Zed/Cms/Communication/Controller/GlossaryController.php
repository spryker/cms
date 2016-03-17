<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Cms\Communication\Controller;

use Generated\Shared\Transfer\CmsBlockTransfer;
use Generated\Shared\Transfer\KeyTranslationTransfer;
use Generated\Shared\Transfer\LocaleTransfer;
use Generated\Shared\Transfer\PageKeyMappingTransfer;
use Generated\Shared\Transfer\PageTransfer;
use Orm\Zed\Cms\Persistence\Base\SpyCmsBlock;
use Orm\Zed\Cms\Persistence\Base\SpyCmsPage;
use Orm\Zed\Glossary\Persistence\SpyGlossaryKey;
use Spryker\Shared\Cms\CmsConstants;
use Spryker\Zed\Application\Communication\Controller\AbstractController;
use Spryker\Zed\Cms\Business\Exception\MissingPageException;
use Spryker\Zed\Cms\Communication\Form\CmsGlossaryForm;
use Spryker\Zed\Cms\Communication\Table\CmsGlossaryTable;
use Spryker\Zed\Cms\Communication\Table\CmsPageTable;
use Spryker\Zed\Cms\Persistence\CmsQueryContainer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

/**
 * @method \Spryker\Zed\Cms\Communication\CmsCommunicationFactory getFactory()
 * @method \Spryker\Zed\Cms\Persistence\CmsQueryContainer getQueryContainer()
 * @method \Spryker\Zed\Cms\Business\CmsFacade getFacade()
 */
class GlossaryController extends AbstractController
{

    const REDIRECT_ADDRESS = '/cms/glossary';
    const SEARCH_LIMIT = 10;
    const ID_FORM = 'id-form';
    const TYPE = 'type';

    /**
     * @var string
     */
    protected $glossaryKeyName = '';

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array
     */
    public function indexAction(Request $request)
    {
        $idPage = $this->castId($request->get(CmsPageTable::REQUEST_ID_PAGE));
        $idForm = (int)$request->get(self::ID_FORM);
        $type = CmsConstants::RESOURCE_TYPE_PAGE;

        $block = $this->getQueryContainer()->queryBlockByIdPage($idPage)->findOne();
        $cmsPage = $this->findCmsPageById($idPage);
        $localeTransfer = $this->getFactory()->getLocaleFacade()->getCurrentLocale();

        $fkLocale = $this->getLocaleByCmsPage($cmsPage);

        if ($block === null) {
            $title = $cmsPage->getUrl();
        } else {
            $type = CmsConstants::RESOURCE_TYPE_BLOCK;
            $title = $block->getName();
        }

        $placeholders = $this->findPagePlaceholders($cmsPage);
        $glossaryMappingArray = $this->extractGlossaryMapping($idPage, $localeTransfer);
        $this->touchNecessaryBlock($idPage);
        $forms = [];
        $formViews = [];

        foreach ($placeholders as $place) {
            $form = $this->createPlaceholderForm($request, $glossaryMappingArray, $place, $idPage, $fkLocale);
            $forms[] = $form;
            $formViews[] = $form->createView();
        }

        if ($idForm !== null && $request->isXmlHttpRequest()) {
            return $this->handleAjaxRequest($forms, $idForm, $localeTransfer);
        }

        return [
            'idPage' => $idPage,
            'title' => $title,
            'type' => $type,
            'forms' => $formViews,
        ];
    }

    /**
     * @param \Orm\Zed\Cms\Persistence\Base\SpyCmsPage $cmsPage
     *
     * @return int|null
     */
    public function getLocaleByCmsPage(SpyCmsPage $cmsPage)
    {
        $fkLocale = null;
        $url = $this->getQueryContainer()
            ->queryUrlById($cmsPage->getIdCmsPage())
            ->findOne();

        if ($url) {
            $fkLocale = $url->getFkLocale();
        }

        return $fkLocale;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Request $request)
    {
        if (!$request->isMethod(Request::METHOD_DELETE)) {
            throw new MethodNotAllowedHttpException([Request::METHOD_DELETE], 'This action requires a DELETE request.');
        }

        $idMapping = $this->castId($request->request->get(CmsGlossaryTable::REQUEST_ID_MAPPING));
        $idPage = $this->castId($request->request->get(CmsPageTable::REQUEST_ID_PAGE));

        $mappingGlossary = $this->getQueryContainer()
            ->queryGlossaryKeyMappingById($idMapping)
            ->findOne();
        $pageTransfer = (new PageTransfer())->setIdCmsPage($idPage);
        $this->getFacade()
            ->deletePageKeyMapping($pageTransfer, $mappingGlossary->getPlaceholder());

        $redirectUrl = self::REDIRECT_ADDRESS . '?' . CmsPageTable::REQUEST_ID_PAGE . '=' . $idPage;

        return $this->redirectResponse($redirectUrl);
    }

    /**
     * @param string $tempFile
     *
     * @return array
     */
    protected function findTemplatePlaceholders($tempFile)
    {
        $placeholderMap = [];

        if (file_exists($tempFile)) {
            $fileContent = file_get_contents($tempFile);

            preg_match_all('/<!-- CMS_PLACEHOLDER : "[a-zA-Z0-9_-]*" -->/', $fileContent, $cmsPlaceholderLine);
            preg_match_all('/"([^"]+)"/', implode(' ', $cmsPlaceholderLine[0]), $placeholderMap);

            return $placeholderMap[1];
        }

        return $placeholderMap;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function searchAction(Request $request)
    {
        $value = $request->query->get('value');
        $key = $request->query->get('key');
        $localeId = $this->castId($request->query->get('localeId'));

        $searchedItems = $this->searchGlossaryKeysAndTranslations($value, $key, $localeId);

        $result = [];
        foreach ($searchedItems as $trans) {
            $result[] = [
                'key' => $trans->getLabel(),
                'value' => $trans->getValue(),
            ];
        }

        return $this->jsonResponse($result);
    }

    /**
     * @param string $value
     * @param string $key
     * @param int $localeId
     *
     * @return \Orm\Zed\Glossary\Persistence\SpyGlossaryKey[]|\Orm\Zed\Glossary\Persistence\SpyGlossaryTranslation[]
     */
    protected function searchGlossaryKeysAndTranslations($value, $key, $localeId)
    {
        $searchedItems = [];
        if ($value !== null) {
            $searchedItems = $this->getQueryContainer()
                ->queryTranslationWithKeyByValue($value)
                ->limit(self::SEARCH_LIMIT)
                ->find();

            return $searchedItems;
        } elseif ($key !== null) {
            $searchedItems = $this->getQueryContainer()
                ->queryKeyWithTranslationByKeyAndLocale($key, $localeId)
                ->limit(self::SEARCH_LIMIT)
                ->find();
        }

        return $searchedItems;
    }

    /**
     * @param array $data
     *
     * @return \Generated\Shared\Transfer\PageKeyMappingTransfer
     */
    protected function createKeyMappingTransfer(array $data)
    {
        $pageKeyMappingTransfer = (new PageKeyMappingTransfer())->fromArray($data, true);
        $hasPageMapping = $this->getFacade()->hasPagePlaceholderMapping($data['fkPage'], $data['placeholder']);
        if ($hasPageMapping) {
            $pageKeyMappingFound = $this->getFacade()
                ->getPagePlaceholderMapping($data['fkPage'], $data['placeholder']);
            $pageKeyMappingTransfer->setIdCmsGlossaryKeyMapping($pageKeyMappingFound->getIdCmsGlossaryKeyMapping());
        }
        $glossaryKey = $this->getQueryContainer()
            ->queryKey($this->glossaryKeyName)
            ->findOne();
        $pageKeyMappingTransfer->setFkGlossaryKey($glossaryKey->getIdGlossaryKey());

        return $pageKeyMappingTransfer;
    }

    /**
     * @param array $data
     * @param \Generated\Shared\Transfer\LocaleTransfer $localeTransfer
     *
     * @return void
     */
    protected function saveGlossaryKeyPageMapping(array $data, LocaleTransfer $localeTransfer)
    {
        $keyTranslationTransfer = $this->createKeyTranslationTransfer($data, $localeTransfer);
        $this->getFactory()->getGlossaryFacade()
            ->saveGlossaryKeyTranslations($keyTranslationTransfer);
        $pageKeyMappingTransfer = $this->createKeyMappingTransfer($data);
        $this->getFacade()
            ->savePageKeyMappingAndTouch($pageKeyMappingTransfer);
    }

    /**
     * @param \Orm\Zed\Cms\Persistence\Base\SpyCmsPage $pageUrl
     *
     * @return array
     */
    protected function findPagePlaceholders(SpyCmsPage $pageUrl)
    {
        $pageUrlArray = $pageUrl->toArray();
        $tempFile = $this->getFactory()
            ->getTemplateRealPath($pageUrlArray[CmsQueryContainer::TEMPLATE_PATH]);
        $placeholders = $this->findTemplatePlaceholders($tempFile);

        return $placeholders;
    }

    /**
     * @param int $idPage
     * @param \Generated\Shared\Transfer\LocaleTransfer $localeTransfer
     *
     * @return array
     */
    protected function extractGlossaryMapping($idPage, LocaleTransfer $localeTransfer)
    {
        $glossaryQuery = $this->getQueryContainer()
            ->queryGlossaryKeyMappingsWithKeyByPageId($idPage, $localeTransfer->getIdLocale());
        $glossaryMappingArray = [];

        /** @var \Orm\Zed\Cms\Persistence\SpyCmsGlossaryKeyMapping[] $keyMappings */
        $keyMappings = $glossaryQuery->find()
            ->getData();
        foreach ($keyMappings as $keyMapping) {
            $glossaryMappingArray[$keyMapping->getPlaceholder()] = $keyMapping->getIdCmsGlossaryKeyMapping();
        }

        return $glossaryMappingArray;
    }

    /**
     * @param array $forms
     * @param int $idForm
     * @param \Generated\Shared\Transfer\LocaleTransfer $localeTransfer
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    protected function handleAjaxRequest(array $forms, $idForm, LocaleTransfer $localeTransfer)
    {
        if ($forms[$idForm]->isValid()) {
            $data = $forms[$idForm]->getData();
            $this->saveGlossaryKeyPageMapping($data, $localeTransfer);

            return $this->jsonResponse([
                'success' => 'true',
                'glossaryKeyName' => $this->glossaryKeyName,
                'data' => $data,
            ]);
        } else {
            return $this->jsonResponse([
                'success' => 'false',
                'errorMessages' => $forms[$idForm]->getErrors()
                    ->__toString(),
            ]);
        }
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param array $glossaryMappingArray
     * @param string $placeholder
     * @param int $idPage
     * @param int $fkLocale
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    protected function createPlaceholderForm(Request $request, array $glossaryMappingArray, $placeholder, $idPage, $fkLocale)
    {
        $idMapping = null;
        if (isset($glossaryMappingArray[$placeholder])) {
            $idMapping = $glossaryMappingArray[$placeholder];
        }

        $dataProvider = $this->getFactory()->createCmsGlossaryFormDataProvider();
        $form = $this->getFactory()
            ->createCmsGlossaryForm(
                $this->getFacade(),
                $dataProvider->getData($idPage, $idMapping, $placeholder, $fkLocale)
            )
            ->handleRequest($request);

        return $form;
    }

    /**
     * @param array $data
     * @param \Generated\Shared\Transfer\LocaleTransfer $localeTransfer
     *
     * @return \Generated\Shared\Transfer\KeyTranslationTransfer
     */
    protected function createKeyTranslationTransfer(array $data, LocaleTransfer $localeTransfer)
    {
        $this->glossaryKeyName = $data[CmsGlossaryForm::FIELD_GLOSSARY_KEY];

        if ($this->glossaryKeyName === null) {
            $this->glossaryKeyName = $this->getFacade()
                ->generateGlossaryKeyName($data[CmsGlossaryForm::FIELD_TEMPLATE_NAME], $data[CmsGlossaryForm::FIELD_PLACEHOLDER]);
        }

        $keyTranslationTransfer = new KeyTranslationTransfer();
        $keyTranslationTransfer->setGlossaryKey($this->glossaryKeyName);

        $keyTranslationTransfer->setLocales([
            $localeTransfer->getLocaleName() => $data[CmsGlossaryForm::FIELD_TRANSLATION],
        ]);

        return $keyTranslationTransfer;
    }

    /**
     * @param int $idPage
     *
     * @throws \Spryker\Zed\Cms\Business\Exception\MissingPageException
     *
     * @return \Orm\Zed\Cms\Persistence\Base\SpyCmsPage
     */
    protected function findCmsPageById($idPage)
    {
        $cmsPage = $this->getQueryContainer()
            ->queryPageWithTemplatesAndUrlByIdPage($idPage)
            ->findOne();

        if ($cmsPage === null) {
            throw new MissingPageException(
                sprintf('Page with id %s not found', $idPage)
            );
        }

        return $cmsPage;
    }

    /**
     * @param int $idPage
     *
     * @return void
     */
    protected function touchNecessaryBlock($idPage)
    {
        $blockEntity = $this->getQueryContainer()
            ->queryBlockByIdPage($idPage)
            ->findOne();

        if ($blockEntity !== null) {
            $blockTransfer = $this->createBlockTransfer($blockEntity);
            $this->getFacade()
                ->touchBlockActive($blockTransfer);
        }
    }

    /**
     * @param \Orm\Zed\Cms\Persistence\Base\SpyCmsBlock $blockEntity
     *
     * @return \Generated\Shared\Transfer\CmsBlockTransfer
     */
    protected function createBlockTransfer(SpyCmsBlock $blockEntity)
    {
        $blockTransfer = new CmsBlockTransfer();
        $blockTransfer->fromArray($blockEntity->toArray());

        return $blockTransfer;
    }

}
