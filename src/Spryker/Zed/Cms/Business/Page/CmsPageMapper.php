<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Cms\Business\Page;

use Generated\Shared\Transfer\CmsPageAttributesTransfer;
use Generated\Shared\Transfer\CmsPageMetaAttributesTransfer;
use Generated\Shared\Transfer\CmsPageTransfer;
use Generated\Shared\Transfer\StoreRelationTransfer;
use Orm\Zed\Cms\Persistence\SpyCmsPage;
use Orm\Zed\Cms\Persistence\SpyCmsPageLocalizedAttributes;
use Spryker\Zed\Cms\Business\Page\Store\CmsPageStoreRelationReaderInterface;

class CmsPageMapper implements CmsPageMapperInterface
{
    /**
     * @var \Spryker\Zed\Cms\Business\Page\CmsPageUrlBuilderInterface
     */
    protected $cmsPageUrlBuilder;

    /**
     * @var \Spryker\Zed\Cms\Business\Page\Store\CmsPageStoreRelationReaderInterface
     */
    protected $cmsPageStoreRelationReader;

    public function __construct(
        CmsPageUrlBuilderInterface $cmsPageUrlBuilder,
        CmsPageStoreRelationReaderInterface $cmsPageStoreRelationReader
    ) {
        $this->cmsPageUrlBuilder = $cmsPageUrlBuilder;
        $this->cmsPageStoreRelationReader = $cmsPageStoreRelationReader;
    }

    /**
     * @param \Orm\Zed\Cms\Persistence\SpyCmsPage $cmsPageEntity
     *
     * @return array<string>
     */
    public function mapCmsPageUrlLocale(SpyCmsPage $cmsPageEntity): array
    {
        $urlLocaleMap = [];
        /** @var array<\Orm\Zed\Url\Persistence\SpyUrl> $urlEntities */
        $urlEntities = $cmsPageEntity->getSpyUrls();
        foreach ($urlEntities as $urlEntity) {
            $urlLocaleMap[$urlEntity->getFkLocale()] = $urlEntity->getUrl();
        }

        return $urlLocaleMap;
    }

    public function mapCmsLocalizedAttributesTransfer(
        SpyCmsPageLocalizedAttributes $cmsPageLocalizedAttributesEntity,
        ?string $url = null
    ): CmsPageAttributesTransfer {
        $localeEntity = $cmsPageLocalizedAttributesEntity->getLocale();
        $cmsPageAttributesTransfer = new CmsPageAttributesTransfer();
        $cmsPageAttributesTransfer->fromArray($cmsPageLocalizedAttributesEntity->toArray(), true);
        $cmsPageAttributesTransfer->setIdCmsPage($cmsPageLocalizedAttributesEntity->getFkCmsPage());
        $cmsPageAttributesTransfer->setLocaleName($localeEntity->getLocaleName());
        $cmsPageAttributesTransfer->setUrl($url);
        $cmsPageAttributesTransfer->setUrlPrefix(
            $this->cmsPageUrlBuilder->getPageUrlPrefix($cmsPageAttributesTransfer),
        );

        return $cmsPageAttributesTransfer;
    }

    public function mapCmsPageMetaAttributes(SpyCmsPageLocalizedAttributes $cmsPageLocalizedAttributesEntity): CmsPageMetaAttributesTransfer
    {
        $localeEntity = $cmsPageLocalizedAttributesEntity->getLocale();
        $cmsCmsPageMetaAttributes = new CmsPageMetaAttributesTransfer();
        $cmsCmsPageMetaAttributes->fromArray($cmsPageLocalizedAttributesEntity->toArray(), true);
        $cmsCmsPageMetaAttributes->setLocaleName($localeEntity->getLocaleName());

        return $cmsCmsPageMetaAttributes;
    }

    public function mapCmsPageTransfer(SpyCmsPage $cmsPageEntity): CmsPageTransfer
    {
        $cmsPageTransfer = new CmsPageTransfer();
        $cmsPageTransfer->setTemplateName($cmsPageEntity->getCmsTemplate()->getTemplateName());
        $cmsPageTransfer->setFkPage($cmsPageEntity->getIdCmsPage());
        $cmsPageTransfer->fromArray($cmsPageEntity->toArray(), true);

        $storeRelationTransfer = $this->cmsPageStoreRelationReader->getStoreRelation(
            (new StoreRelationTransfer())->setIdEntity($cmsPageEntity->getIdCmsPage()),
        );

        $cmsPageTransfer->setStoreRelation($storeRelationTransfer);

        return $cmsPageTransfer;
    }
}
