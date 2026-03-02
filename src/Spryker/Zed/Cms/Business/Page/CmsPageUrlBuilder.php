<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Cms\Business\Page;

use Generated\Shared\Transfer\CmsPageAttributesTransfer;
use Spryker\Zed\Cms\CmsConfig;

class CmsPageUrlBuilder implements CmsPageUrlBuilderInterface
{
    /**
     * @var \Spryker\Zed\Cms\CmsConfig
     */
    protected $cmsConfig;

    public function __construct(CmsConfig $cmsConfig)
    {
        $this->cmsConfig = $cmsConfig;
    }

    public function buildPageUrl(CmsPageAttributesTransfer $cmsPageAttributesTransfer): string
    {
        $cmsPageAttributesTransfer->requireUrl()
            ->requireLocaleName();

        $prefix = $this->getPageUrlPrefix($cmsPageAttributesTransfer);

        if (!$prefix) {
            return $cmsPageAttributesTransfer->getUrl();
        }

        $url = $cmsPageAttributesTransfer->getUrl();
        if (preg_match('#^' . $prefix . '#i', $url) > 0) {
            return $url;
        }

        $url = preg_replace('#^/#', '', $url);

        $urlWithPrefix = $prefix . $url;

        return $urlWithPrefix;
    }

    public function getPageUrlPrefix(CmsPageAttributesTransfer $cmsPageAttributesTransfer): string
    {
        if (!$this->cmsConfig->appendPrefixToCmsPageUrl()) {
            return '';
        }

        return '/' . $this->resolvePrefix($cmsPageAttributesTransfer) . '/';
    }

    protected function resolvePrefix(CmsPageAttributesTransfer $cmsPageAttributesTransfer): string
    {
        $cmsPageAttributesTransfer->requireLocaleName();

        $prefix = $this->cmsConfig->isFullLocaleNamesInUrlEnabled()
            ? str_replace('_', '-', strtolower($cmsPageAttributesTransfer->getLocaleName()))
            : $this->extractLanguageCode($cmsPageAttributesTransfer->getLocaleName());

        return strtolower($prefix);
    }

    protected function extractLanguageCode(string $localeName): string
    {
        $localeNameParts = explode('_', $localeName);
        $languageCode = $localeNameParts[0];

        return $languageCode;
    }
}
