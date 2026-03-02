<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Cms\Business\Page;

use Generated\Shared\Transfer\CmsPageAttributesTransfer;

interface CmsPageUrlBuilderInterface
{
    public function buildPageUrl(CmsPageAttributesTransfer $cmsPageAttributesTransfer): string;

    public function getPageUrlPrefix(CmsPageAttributesTransfer $cmsPageAttributesTransfer): string;
}
