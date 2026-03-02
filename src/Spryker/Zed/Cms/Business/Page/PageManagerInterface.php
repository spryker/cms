<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Cms\Business\Page;

use Generated\Shared\Transfer\LocaleTransfer;
use Generated\Shared\Transfer\PageTransfer;
use Generated\Shared\Transfer\UrlTransfer;
use Orm\Zed\Cms\Persistence\SpyCmsPage;

interface PageManagerInterface
{
    /**
     * @deprecated Will be removed in the next major release.
     *
     * @param \Generated\Shared\Transfer\PageTransfer $pageTransfer
     *
     * @throws \Spryker\Zed\Cms\Business\Exception\MissingTemplateException
     * @throws \Spryker\Zed\Cms\Business\Exception\MissingPageException
     * @throws \Spryker\Zed\Url\Business\Exception\MissingUrlException
     * @throws \Spryker\Zed\Cms\Business\Exception\PageExistsException
     *
     * @return \Generated\Shared\Transfer\PageTransfer
     */
    public function savePage(PageTransfer $pageTransfer): PageTransfer;

    /**
     * @param int $idPage
     *
     * @throws \Spryker\Zed\Cms\Business\Exception\MissingPageException
     *
     * @return \Orm\Zed\Cms\Persistence\SpyCmsPage
     */
    public function getPageById(int $idPage): SpyCmsPage;

    public function convertPageEntityToTransfer(SpyCmsPage $pageEntity): PageTransfer;

    public function touchPageActive(PageTransfer $pageTransfer, ?LocaleTransfer $localeTransfer = null): void;

    /**
     * @param \Generated\Shared\Transfer\PageTransfer $pageTransfer
     *
     * @throws \Spryker\Zed\Url\Business\Exception\UrlExistsException
     *
     * @return \Generated\Shared\Transfer\UrlTransfer
     */
    public function createPageUrl(PageTransfer $pageTransfer): UrlTransfer;

    /**
     * @param \Generated\Shared\Transfer\PageTransfer $pageTransfer
     *
     * @throws \Spryker\Zed\Url\Business\Exception\UrlExistsException
     *
     * @return \Generated\Shared\Transfer\UrlTransfer
     */
    public function updatePageUrl(PageTransfer $pageTransfer): UrlTransfer;

    /**
     * @param \Generated\Shared\Transfer\PageTransfer $pageTransfer
     * @param string $url
     * @param \Generated\Shared\Transfer\LocaleTransfer $localeTransfer
     *
     * @throws \Spryker\Zed\Url\Business\Exception\UrlExistsException
     *
     * @return \Generated\Shared\Transfer\UrlTransfer
     */
    public function createPageUrlWithLocale(PageTransfer $pageTransfer, string $url, LocaleTransfer $localeTransfer): UrlTransfer;

    public function savePageUrlAndTouch(PageTransfer $pageTransfer): UrlTransfer;
}
