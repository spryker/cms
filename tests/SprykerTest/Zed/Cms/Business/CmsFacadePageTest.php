<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Cms\Business;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CmsPageAttributesTransfer;
use Generated\Shared\Transfer\CmsPageMetaAttributesTransfer;
use Generated\Shared\Transfer\CmsPageTransfer;
use Generated\Shared\Transfer\StoreRelationTransfer;
use Spryker\Zed\Cms\Business\CmsFacade;
use Spryker\Zed\Store\Business\StoreFacade;
use Spryker\Zed\Store\StoreDependencyProvider;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Cms
 * @group Business
 * @group Facade
 * @group CmsFacadePageTest
 * Add your own group annotations below this line
 */
class CmsFacadePageTest extends Unit
{
    /**
     * @var string
     */
    public const CMS_PAGE_NEW_TITLE = 'new title';

    /**
     * @var string
     */
    public const CMS_PAGE_NEW_KEY_WORDS = 'new key words';

    /**
     * @var string
     */
    public const CMS_PAGE_NEW_DESCRIPTION = 'new description';

    /**
     * @var \Spryker\Zed\Cms\Business\CmsFacade
     */
    protected $cmsFacade;

    /**
     * @var \SprykerTest\Zed\Cms\CmsBusinessTester
     */
    protected $tester;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cmsFacade = new CmsFacade();
    }

    public function testSaveCmsGlossaryShouldPersistUpdatedTranslations(): void
    {
        $fixtures = $this->createCmsPageTransferFixtures();
        $cmsPageTransfer = $this->createCmsPageTransfer($fixtures);

        $idCmsPage = $this->cmsFacade->createPage($cmsPageTransfer);

        $cmsGlossaryTransfer = $this->cmsFacade->findPageGlossaryAttributes($idCmsPage);

        $cmsGlossaryAttributesTransfer = $cmsGlossaryTransfer->getGlossaryAttributes()[0];

        $translationFixtures = $this->getTranslationFixtures();

        $translations = $cmsGlossaryAttributesTransfer->getTranslations();
        foreach ($translations as $cmsPlaceholderTranslationTransfer) {
            $cmsPlaceholderTranslationTransfer->setTranslation(
                $translationFixtures[$cmsPlaceholderTranslationTransfer->getLocaleName()],
            );
        }

        $updatedCmsGlossaryTransfer = $this->cmsFacade->saveCmsGlossary($cmsGlossaryTransfer);

        $cmsGlossaryAttributesTransfer = $updatedCmsGlossaryTransfer->getGlossaryAttributes()[0];

        $translations = $cmsGlossaryAttributesTransfer->getTranslations();
        foreach ($translations as $cmsPlaceholderTranslationTransfer) {
            $this->assertSame(
                $translationFixtures[$cmsPlaceholderTranslationTransfer->getLocaleName()],
                $cmsPlaceholderTranslationTransfer->getTranslation(),
            );
        }
    }

    public function testCreatePageShouldPersistGivenCmsPage(): void
    {
        $fixtures = $this->createCmsPageTransferFixtures();
        $cmsPageTransfer = $this->createCmsPageTransfer($fixtures);

        $idCmsPage = $this->cmsFacade->createPage($cmsPageTransfer);
        $persistedCmsPageTransfer = $this->cmsFacade->findCmsPageById($idCmsPage);

        $this->assertSame($persistedCmsPageTransfer->getFkTemplate(), $cmsPageTransfer->getFkTemplate());
        $this->assertSame($persistedCmsPageTransfer->getIsActive(), $cmsPageTransfer->getIsActive());
        $this->assertSame($persistedCmsPageTransfer->getIsSearchable(), $cmsPageTransfer->getIsSearchable());
        $this->assertNotEmpty($persistedCmsPageTransfer->getFkPage());

        $this->assertPageAttributes($cmsPageTransfer, $persistedCmsPageTransfer);
        $this->assertPageMetaAttributes($cmsPageTransfer, $persistedCmsPageTransfer);
    }

    public function testUpdatePageShouldUpdatePageWithNewData(): void
    {
        $fixtures = $this->createCmsPageTransferFixtures();
        $cmsPageTransfer = $this->createCmsPageTransfer($fixtures);

        $idCmsPage = $this->cmsFacade->createPage($cmsPageTransfer);
        $persistedCmsPageTransfer = $this->cmsFacade->findCmsPageById($idCmsPage);

        $persistedCmsPageMetaAttributes = $persistedCmsPageTransfer->getMetaAttributes()[0];
        $persistedCmsPageMetaAttributes->setMetaTitle(static::CMS_PAGE_NEW_TITLE);
        $persistedCmsPageMetaAttributes->setMetaKeywords(static::CMS_PAGE_NEW_KEY_WORDS);
        $persistedCmsPageMetaAttributes->setMetaDescription(static::CMS_PAGE_NEW_DESCRIPTION);

        $persistedCmsPageAttributes = $persistedCmsPageTransfer->getPageAttributes()[0];
        $persistedCmsPageAttributes->setName('new page name');
        $persistedCmsPageAttributes->setUrl('updated-url');

        $updatedCmsPageTransfer = $this->cmsFacade->updatePage($persistedCmsPageTransfer);

        $updatedCmsPageMetaAttributes = $updatedCmsPageTransfer->getMetaAttributes()[0];
        $this->assertSame($updatedCmsPageMetaAttributes->getMetaDescription(), $persistedCmsPageMetaAttributes->getMetaDescription());
        $this->assertSame($updatedCmsPageMetaAttributes->getMetaKeywords(), $persistedCmsPageMetaAttributes->getMetaKeywords());
        $this->assertSame($updatedCmsPageMetaAttributes->getMetaTitle(), $persistedCmsPageMetaAttributes->getMetaTitle());

        $updatedCmsPageAttributes = $persistedCmsPageTransfer->getPageAttributes()[0];
        $this->assertSame($updatedCmsPageAttributes->getName(), $persistedCmsPageAttributes->getName());
        $this->assertSame($updatedCmsPageAttributes->getUrl(), $persistedCmsPageAttributes->getUrl());
    }

    public function testActivatePageShouldActivateInactivePage(): void
    {
        $fixtures = $this->createCmsPageTransferFixtures();
        $fixtures[CmsPageTransfer::IS_ACTIVE] = false;
        $cmsPageTransfer = $this->createCmsPageTransfer($fixtures);

        $idCmsPage = $this->cmsFacade->createPage($cmsPageTransfer);

        $cmsGlossaryTransfer = $this->cmsFacade->findPageGlossaryAttributes($idCmsPage);

        $cmsGlossaryAttributesTransfer = $cmsGlossaryTransfer->getGlossaryAttributes()[0];

        $translationFixtures = $this->getTranslationFixtures();

        $translations = $cmsGlossaryAttributesTransfer->getTranslations();
        foreach ($translations as $cmsPlaceholderTranslationTransfer) {
            $cmsPlaceholderTranslationTransfer->setTranslation(
                $translationFixtures[$cmsPlaceholderTranslationTransfer->getLocaleName()],
            );
        }
        $this->cmsFacade->saveCmsGlossary($cmsGlossaryTransfer);

        $this->cmsFacade->activatePage($idCmsPage);

        $persistedCmsPageTransfer = $this->cmsFacade->findCmsPageById($idCmsPage);

        $this->assertTrue($persistedCmsPageTransfer->getIsActive());
    }

    public function testDeActivatePageShouldActivateInactivePage(): void
    {
        $fixtures = $this->createCmsPageTransferFixtures();
        $cmsPageTransfer = $this->createCmsPageTransfer($fixtures);

        $idCmsPage = $this->cmsFacade->createPage($cmsPageTransfer);

        $this->cmsFacade->deactivatePage($idCmsPage);

        $persistedCmsPageTransfer = $this->cmsFacade->findCmsPageById($idCmsPage);

        $this->assertFalse($persistedCmsPageTransfer->getIsActive());
    }

    public function testGetPageUrlPrefixShouldBuildUrlPrefixFromGivenLocalName(): void
    {
        $cmsPageAttributeTransfer = new CmsPageAttributesTransfer();
        $cmsPageAttributeTransfer->setLocaleName('en_US');

        $urlPrefix = $this->cmsFacade->getPageUrlPrefix($cmsPageAttributeTransfer);

        $this->assertSame('', $urlPrefix);
    }

    public function testBuildPageUrlWhenUrlWithoutPrefixGivenShouldBuildValidUrl(): void
    {
        $cmsPageAttributesTransfer = new CmsPageAttributesTransfer();
        $cmsPageAttributesTransfer->setLocaleName('en_US');
        $cmsPageAttributesTransfer->setUrl('test-url-functionl');

        $url = $this->cmsFacade->buildPageUrl($cmsPageAttributesTransfer);

        $this->assertSame($cmsPageAttributesTransfer->getUrl(), $url);
    }

    public function testBuildPageUrlWhenUrlWithPrefixGivenShouldBuildValidUrl(): void
    {
        $cmsPageAttributesTransfer = new CmsPageAttributesTransfer();
        $cmsPageAttributesTransfer->setLocaleName('en_US');
        $cmsPageAttributesTransfer->setUrl('/en/test-url-functionl');

        $url = $this->cmsFacade->buildPageUrl($cmsPageAttributesTransfer);

        $this->assertSame($cmsPageAttributesTransfer->getUrl(), $url);
    }

    public function testPublishPageShouldPersistCmsVersion(): void
    {
        $idCmsPage = $this->createCmsPageWithGlossaryAttributes();
        $cmsVersionTransfer = $this->cmsFacade->publishWithVersion($idCmsPage);

        $this->assertNotNull($cmsVersionTransfer);
        $this->assertSame($cmsVersionTransfer->getFkCmsPage(), $idCmsPage);
        $this->assertSame($cmsVersionTransfer->getVersion(), 1);
        $this->assertNotEmpty($cmsVersionTransfer->getData());
    }

    public function testPublishPageShouldGetNewVersion(): void
    {
        $idCmsPage = $this->createCmsPageWithGlossaryAttributes();
        $cmsVersionTransferOne = $this->cmsFacade->publishWithVersion($idCmsPage);
        $cmsVersionTransferTwo = $this->cmsFacade->publishWithVersion($idCmsPage);

        $this->assertGreaterThan($cmsVersionTransferOne->getVersion(), $cmsVersionTransferTwo->getVersion());
    }

    public function testRollbackPageShouldGetOldData(): void
    {
        $idCmsPage = $this->createCmsPageWithGlossaryAttributes();
        $cmsVersionTransferOne = $this->cmsFacade->publishWithVersion($idCmsPage);

        $persistedCmsPageTransfer = $this->cmsFacade->findCmsPageById($idCmsPage);

        foreach ($persistedCmsPageTransfer->getMetaAttributes() as $metaAttribute) {
            $metaAttribute->setMetaTitle(static::CMS_PAGE_NEW_TITLE);
            $metaAttribute->setMetaKeywords(static::CMS_PAGE_NEW_KEY_WORDS);
            $metaAttribute->setMetaDescription(static::CMS_PAGE_NEW_DESCRIPTION);
        }

        $updatedPageTransfer = $this->cmsFacade->updatePage($persistedCmsPageTransfer);
        $updatedCmsPageMetaAttributes = $updatedPageTransfer->getMetaAttributes()[0];

        $this->assertSame($updatedCmsPageMetaAttributes->getMetaDescription(), static::CMS_PAGE_NEW_DESCRIPTION);
        $this->assertSame($updatedCmsPageMetaAttributes->getMetaKeywords(), static::CMS_PAGE_NEW_KEY_WORDS);
        $this->assertSame($updatedCmsPageMetaAttributes->getMetaTitle(), static::CMS_PAGE_NEW_TITLE);

        $this->cmsFacade->publishWithVersion($idCmsPage);
        $this->cmsFacade->rollback($idCmsPage, $cmsVersionTransferOne->getVersion());

        $persistedCmsPageTransfer = $this->cmsFacade->findCmsPageById($idCmsPage);
        $persistedCmsPageMetaAttributes = $persistedCmsPageTransfer->getMetaAttributes()[0];

        $this->assertNotEquals($persistedCmsPageMetaAttributes->getMetaDescription(), static::CMS_PAGE_NEW_DESCRIPTION);
        $this->assertNotEquals($persistedCmsPageMetaAttributes->getMetaKeywords(), static::CMS_PAGE_NEW_KEY_WORDS);
        $this->assertNotEquals($persistedCmsPageMetaAttributes->getMetaTitle(), static::CMS_PAGE_NEW_TITLE);
    }

    public function testRevertPageShouldGetOldData(): void
    {
        $idCmsPage = $this->createCmsPageWithGlossaryAttributes();
        $this->cmsFacade->publishWithVersion($idCmsPage);

        $persistedCmsPageTransfer = $this->cmsFacade->findCmsPageById($idCmsPage);

        foreach ($persistedCmsPageTransfer->getMetaAttributes() as $metaAttribute) {
            $metaAttribute->setMetaTitle(static::CMS_PAGE_NEW_TITLE);
            $metaAttribute->setMetaKeywords(static::CMS_PAGE_NEW_KEY_WORDS);
            $metaAttribute->setMetaDescription(static::CMS_PAGE_NEW_DESCRIPTION);
        }

        $updatedPageTransfer = $this->cmsFacade->updatePage($persistedCmsPageTransfer);
        $updatedCmsPageMetaAttributes = $updatedPageTransfer->getMetaAttributes()[0];

        $this->assertSame($updatedCmsPageMetaAttributes->getMetaDescription(), static::CMS_PAGE_NEW_DESCRIPTION);
        $this->assertSame($updatedCmsPageMetaAttributes->getMetaKeywords(), static::CMS_PAGE_NEW_KEY_WORDS);
        $this->assertSame($updatedCmsPageMetaAttributes->getMetaTitle(), static::CMS_PAGE_NEW_TITLE);

        $this->cmsFacade->revert($idCmsPage);

        $persistedCmsPageTransfer = $this->cmsFacade->findCmsPageById($idCmsPage);
        $persistedCmsPageMetaAttributes = $persistedCmsPageTransfer->getMetaAttributes()[0];

        $this->assertNotEquals($persistedCmsPageMetaAttributes->getMetaDescription(), static::CMS_PAGE_NEW_DESCRIPTION);
        $this->assertNotEquals($persistedCmsPageMetaAttributes->getMetaKeywords(), static::CMS_PAGE_NEW_KEY_WORDS);
        $this->assertNotEquals($persistedCmsPageMetaAttributes->getMetaTitle(), static::CMS_PAGE_NEW_TITLE);
    }

    public function testFindLatestCmsVersionReturnsLatestVersion(): void
    {
        $idCmsPage = $this->createCmsPageWithGlossaryAttributes();
        $cmsVersionTransferOne = $this->cmsFacade->publishWithVersion($idCmsPage);
        $this->cmsFacade->publishWithVersion($idCmsPage);

        $cmsVersionTransferTwo = $this->cmsFacade->findLatestCmsVersionByIdCmsPage($idCmsPage);

        $this->assertGreaterThan($cmsVersionTransferOne->getVersion(), $cmsVersionTransferTwo->getVersion());
    }

    public function testFindAllCmsVersionByReturnsAllVersions(): void
    {
        $idCmsPage = $this->createCmsPageWithGlossaryAttributes();
        $this->cmsFacade->publishWithVersion($idCmsPage);
        $this->cmsFacade->publishWithVersion($idCmsPage);

        $versions = $this->cmsFacade->findAllCmsVersionByIdCmsPage($idCmsPage);

        $this->assertSame(count($versions), 2);
    }

    public function testFindCmsVersionByVersionNumberReturnsSameVersion(): void
    {
        $idCmsPage = $this->createCmsPageWithGlossaryAttributes();
        $this->cmsFacade->publishWithVersion($idCmsPage);
        $this->cmsFacade->publishWithVersion($idCmsPage);

        $cmsVersion = $this->cmsFacade->findCmsVersionByIdCmsPageAndVersion($idCmsPage, 1);

        $this->assertSame($cmsVersion->getVersion(), 1);
    }

    public function testGetCmsVersionDataRetrievesDraftDataFromDatabase(): void
    {
        // Arrange
        $idCmsPage = $this->createCmsPageWithGlossaryAttributes();
        $persistedCmsPageTransfer = $this->cmsFacade->findCmsPageById($idCmsPage);

        foreach ($persistedCmsPageTransfer->getMetaAttributes() as $metaAttribute) {
            $metaAttribute->setMetaTitle(static::CMS_PAGE_NEW_TITLE);
            $metaAttribute->setMetaKeywords(static::CMS_PAGE_NEW_KEY_WORDS);
            $metaAttribute->setMetaDescription(static::CMS_PAGE_NEW_DESCRIPTION);
        }

        $expectedCmsVersionData = $this->cmsFacade->updatePage($persistedCmsPageTransfer);

        // Act
        $actualCmsVersionData = $this->cmsFacade->getCmsVersionData($idCmsPage);

        // Assert
        $expectedCmsPageVersionMetaAttributes = $expectedCmsVersionData->getMetaAttributes()[0];
        $actualCmsPageVersionMetaAttributes = $actualCmsVersionData->getCmsPage()->getMetaAttributes()[0];
        $this->assertEquals($expectedCmsPageVersionMetaAttributes->getMetaDescription(), $actualCmsPageVersionMetaAttributes->getMetaDescription());
        $this->assertEquals($expectedCmsPageVersionMetaAttributes->getMetaKeywords(), $actualCmsPageVersionMetaAttributes->getMetaKeywords());
        $this->assertEquals($expectedCmsPageVersionMetaAttributes->getMetaTitle(), $actualCmsPageVersionMetaAttributes->getMetaTitle());
    }

    protected function createCmsPageWithGlossaryAttributes(): int
    {
        $fixtures = $this->createCmsPageTransferFixtures();
        $cmsPageTransfer = $this->createCmsPageTransfer($fixtures);

        $idCmsPage = $this->cmsFacade->createPage($cmsPageTransfer);
        $cmsGlossaryTransfer = $this->cmsFacade->findPageGlossaryAttributes($idCmsPage);

        $cmsGlossaryAttributesTransfer = $cmsGlossaryTransfer->getGlossaryAttributes()[0];

        $translationFixtures = $this->getTranslationFixtures();

        $translations = $cmsGlossaryAttributesTransfer->getTranslations();
        foreach ($translations as $cmsPlaceholderTranslationTransfer) {
            $cmsPlaceholderTranslationTransfer->setTranslation($translationFixtures[$cmsPlaceholderTranslationTransfer->getLocaleName()]);
        }
        $this->cmsFacade->saveCmsGlossary($cmsGlossaryTransfer);

        return $idCmsPage;
    }

    public function testCreateCmsPageSavesStoreRelation(): void
    {
        $storeFacade = $this->createStoreFacade();

        $stores = $storeFacade->getAllStores();

        $expectedIdStores = [];

        foreach ($stores as $storeTransfer) {
            $expectedIdStores[] = $storeTransfer->getIdStore();
        }

        $storeRelationSeed = [
            CmsPageTransfer::STORE_RELATION => [
                StoreRelationTransfer::ID_STORES => $expectedIdStores,
            ],
        ];

        $fixtures = $this->createCmsPageTransferFixtures();
        $fixtures += $storeRelationSeed;

        $cmsPageTransfer = $this->createCmsPageTransfer($fixtures);
        $idCmsPage = $this->cmsFacade->createPage($cmsPageTransfer);

        $persistedCmsPageTransfer = $this->cmsFacade->findCmsPageById($idCmsPage);
        $resultIdStores = $persistedCmsPageTransfer->getStoreRelation()->getIdStores();

        sort($resultIdStores);
        $this->assertEquals($expectedIdStores, $resultIdStores);
    }

    /**
     * @dataProvider relationUpdateFixtures
     *
     * @param array<int> $originalRelation
     * @param array<int> $modifiedRelation
     *
     * @return void
     */
    public function testUpdateCmsPageUpdatesStoreRelation(array $originalRelation, array $modifiedRelation): void
    {
        // Arrange
        $this->tester->setDependency(StoreDependencyProvider::PLUGINS_STORE_COLLECTION_EXPANDER, []);

        $originalRelationStoreIds = $this->tester->createStoresByNames($originalRelation);
        $modifiedRelationStoreIds = $this->tester->createStoresByNames($modifiedRelation);
        $storeRelationSeed = [
            CmsPageTransfer::STORE_RELATION => [
                StoreRelationTransfer::ID_STORES => $originalRelationStoreIds,
            ],
        ];

        $fixtures = $this->createCmsPageTransferFixtures();
        $fixtures += $storeRelationSeed;

        $cmsPageTransfer = $this->createCmsPageTransfer($fixtures);
        $idCmsPage = $this->cmsFacade->createPage($cmsPageTransfer);

        $persistedCmsPageTransfer = $this->cmsFacade->findCmsPageById($idCmsPage);
        $persistedCmsPageTransfer->getStoreRelation()->setIdStores($modifiedRelationStoreIds);

        // Act
        $this->cmsFacade->updatePage($persistedCmsPageTransfer);

        $persistedCmsPageTransfer = $this->cmsFacade->findCmsPageById($persistedCmsPageTransfer->getFkPage());
        $resultIdStores = $persistedCmsPageTransfer->getStoreRelation()->getIdStores();

        // Assert
        sort($resultIdStores);
        $this->assertEquals($modifiedRelationStoreIds, $resultIdStores);
    }

    protected function createCmsPageTransfer(array $fixtures): CmsPageTransfer
    {
        $cmsPageTransfer = new CmsPageTransfer();
        $cmsPageTransfer->fromArray($fixtures, true);

        return $cmsPageTransfer;
    }

    protected function createCmsPageTransferFixtures(): array
    {
        $fixtures = [
            CmsPageTransfer::IS_ACTIVE => true,
            CmsPageTransfer::FK_TEMPLATE => 1,
            CmsPageTransfer::IS_SEARCHABLE => true,
            CmsPageTransfer::PAGE_ATTRIBUTES => [
                [
                    CmsPageAttributesTransfer::URL => '/en/function-test',
                    CmsPageAttributesTransfer::NAME => 'functional test',
                    CmsPageAttributesTransfer::LOCALE_NAME => 'en_US',
                    CmsPageAttributesTransfer::URL_PREFIX => '',
                    CmsPageAttributesTransfer::FK_LOCALE => 66,
                ],
                [
                    CmsPageAttributesTransfer::URL => '/de/function-test',
                    CmsPageAttributesTransfer::NAME => 'functional test',
                    CmsPageAttributesTransfer::LOCALE_NAME => 'de_DE',
                    CmsPageAttributesTransfer::URL_PREFIX => '',
                    CmsPageAttributesTransfer::FK_LOCALE => 46,
                ],
            ],
            CmsPageTransfer::META_ATTRIBUTES => [
                [
                    CmsPageMetaAttributesTransfer::META_TITLE => 'title english',
                    CmsPageMetaAttributesTransfer::META_KEYWORDS => 'key, word',
                    CmsPageMetaAttributesTransfer::META_DESCRIPTION => 'english description',
                    CmsPageMetaAttributesTransfer::LOCALE_NAME => 'en_US',
                    CmsPageAttributesTransfer::FK_LOCALE => 66,
                ],
                [
                    CmsPageMetaAttributesTransfer::META_TITLE => 'title german',
                    CmsPageMetaAttributesTransfer::META_KEYWORDS => 'key, word',
                    CmsPageMetaAttributesTransfer::META_DESCRIPTION => 'german description',
                    CmsPageMetaAttributesTransfer::LOCALE_NAME => 'de_DE',
                    CmsPageAttributesTransfer::FK_LOCALE => 46,
                ],
            ],
        ];

        return $fixtures;
    }

    protected function assertPageAttributes(CmsPageTransfer $cmsPageTransfer, CmsPageTransfer $persistedCmsPageTransfer): void
    {
        foreach ($cmsPageTransfer->getPageAttributes() as $cmsPageAttributesTransfer) {
            foreach ($persistedCmsPageTransfer->getPageAttributes() as $persistedCmsPageAttributesTransfer) {
                if ($cmsPageAttributesTransfer->getLocaleName() !== $persistedCmsPageAttributesTransfer->getLocaleName()) {
                    continue;
                }
                $this->assertEquals($cmsPageAttributesTransfer->getName(), $persistedCmsPageAttributesTransfer->getName());
                $this->assertEquals($cmsPageAttributesTransfer->getUrlPrefix(), $persistedCmsPageAttributesTransfer->getUrlPrefix());
                $this->assertEquals($cmsPageAttributesTransfer->getUrl(), $persistedCmsPageAttributesTransfer->getUrl());
                $this->assertEquals($persistedCmsPageTransfer->getFkPage(), $persistedCmsPageAttributesTransfer->getIdCmsPage());
            }
        }
    }

    protected function assertPageMetaAttributes(CmsPageTransfer $cmsPageTransfer, CmsPageTransfer $persistedCmsPageTransfer): void
    {
        foreach ($cmsPageTransfer->getMetaAttributes() as $cmsPageMetaAttributesTransfer) {
            foreach ($persistedCmsPageTransfer->getMetaAttributes() as $persistedCmsPageMetaAttributesTransfer) {
                if ($persistedCmsPageMetaAttributesTransfer->getLocaleName() !== $cmsPageMetaAttributesTransfer->getLocaleName()) {
                    continue;
                }
                $this->assertEquals($cmsPageMetaAttributesTransfer->getMetaDescription(), $persistedCmsPageMetaAttributesTransfer->getMetaDescription());
                $this->assertEquals($cmsPageMetaAttributesTransfer->getMetaTitle(), $persistedCmsPageMetaAttributesTransfer->getMetaTitle());
                $this->assertEquals($cmsPageMetaAttributesTransfer->getMetaKeywords(), $persistedCmsPageMetaAttributesTransfer->getMetaKeywords());
            }
        }
    }

    protected function getTranslationFixtures(): array
    {
        $translationFixtures = [
            'en_US' => 'english translation',
            'de_DE' => 'german translation',
        ];

        return $translationFixtures;
    }

    public function relationUpdateFixtures(): array
    {
        return [
            [
                ['DE', 'AT'], ['AT'],
            ],
            [
                ['DE'], ['DE', 'AT'],
            ],
            [
                ['AT'], ['DE', 'AT'],
            ],
        ];
    }

    protected function createStoreFacade(): StoreFacade
    {
        return new StoreFacade();
    }
}
