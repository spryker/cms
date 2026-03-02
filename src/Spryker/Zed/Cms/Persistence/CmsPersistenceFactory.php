<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Cms\Persistence;

use Orm\Zed\Cms\Persistence\SpyCmsGlossaryKeyMappingQuery;
use Orm\Zed\Cms\Persistence\SpyCmsPageLocalizedAttributesQuery;
use Orm\Zed\Cms\Persistence\SpyCmsPageQuery;
use Orm\Zed\Cms\Persistence\SpyCmsPageStoreQuery;
use Orm\Zed\Cms\Persistence\SpyCmsTemplateQuery;
use Orm\Zed\Cms\Persistence\SpyCmsVersionQuery;
use Orm\Zed\Locale\Persistence\SpyLocaleQuery;
use Orm\Zed\Url\Persistence\SpyUrlQuery;
use Spryker\Zed\Cms\CmsDependencyProvider;
use Spryker\Zed\Kernel\Persistence\AbstractPersistenceFactory;

/**
 * @method \Spryker\Zed\Cms\CmsConfig getConfig()
 * @method \Spryker\Zed\Cms\Persistence\CmsQueryContainerInterface getQueryContainer()
 * @method \Spryker\Zed\Cms\Persistence\CmsRepositoryInterface getRepository()
 * @method \Spryker\Zed\Cms\Persistence\CmsEntityManagerInterface getEntityManager()
 */
class CmsPersistenceFactory extends AbstractPersistenceFactory
{
    public function createCmsTemplateQuery(): SpyCmsTemplateQuery
    {
        return SpyCmsTemplateQuery::create();
    }

    public function createCmsPageQuery(): SpyCmsPageQuery
    {
        return SpyCmsPageQuery::create();
    }

    public function createCmsGlossaryKeyMappingQuery(): SpyCmsGlossaryKeyMappingQuery
    {
        return SpyCmsGlossaryKeyMappingQuery::create();
    }

    public function getLocalePropelQuery(): SpyLocaleQuery
    {
        return $this->getProvidedDependency(CmsDependencyProvider::PROPEL_QUERY_LOCALE);
    }

    public function createCmsPageLocalizedAttributesQuery(): SpyCmsPageLocalizedAttributesQuery
    {
        return SpyCmsPageLocalizedAttributesQuery::create();
    }

    public function createSpyCmsVersionQuery(): SpyCmsVersionQuery
    {
        return SpyCmsVersionQuery::create();
    }

    public function createUrlQuery(): SpyUrlQuery
    {
        return SpyUrlQuery::create();
    }

    public function createCmsPageStoreQuery(): SpyCmsPageStoreQuery
    {
        return SpyCmsPageStoreQuery::create();
    }
}
