<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Cms\Business\Template;

use Generated\Shared\Transfer\CmsTemplateTransfer;

interface TemplateManagerInterface
{
    /**
     * @param string $name
     * @param string $path
     *
     * @throws \Spryker\Zed\Cms\Business\Exception\TemplateExistsException
     *
     * @return \Generated\Shared\Transfer\CmsTemplateTransfer
     */
    public function createTemplate(string $name, string $path): CmsTemplateTransfer;

    public function hasTemplatePath(string $path): bool;

    public function hasTemplateId(int $id): bool;

    public function saveTemplate(CmsTemplateTransfer $cmsTemplate): CmsTemplateTransfer;

    /**
     * @param int $idTemplate
     *
     * @throws \Spryker\Zed\Cms\Business\Exception\MissingTemplateException
     *
     * @return \Generated\Shared\Transfer\CmsTemplateTransfer
     */
    public function getTemplateById(int $idTemplate): CmsTemplateTransfer;

    /**
     * @param string $path
     *
     * @throws \Spryker\Zed\Cms\Business\Exception\MissingTemplateException
     *
     * @return \Generated\Shared\Transfer\CmsTemplateTransfer
     */
    public function getTemplateByPath(string $path): CmsTemplateTransfer;

    public function syncTemplate(string $cmsTemplateFolderPath): bool;

    /**
     * @param string $path
     *
     * @throws \Spryker\Zed\Cms\Business\Exception\TemplateFileNotFoundException
     *
     * @return void
     */
    public function checkTemplateFileExists(string $path): void;
}
