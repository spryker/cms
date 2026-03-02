<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Cms\Business\Extractor;

use Generated\Shared\Transfer\CmsVersionDataTransfer;
use Generated\Shared\Transfer\LocaleCmsPageDataTransfer;
use Generated\Shared\Transfer\LocaleTransfer;

interface DataExtractorInterface
{
    public function extractCmsVersionDataTransfer(string $data): CmsVersionDataTransfer;

    public function extractLocaleCmsPageDataTransfer(CmsVersionDataTransfer $cmsVersionDataTransfer, LocaleTransfer $localeTransfer): LocaleCmsPageDataTransfer;
}
