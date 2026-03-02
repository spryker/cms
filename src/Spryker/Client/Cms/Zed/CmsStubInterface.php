<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\Cms\Zed;

use Generated\Shared\Transfer\FlattenedLocaleCmsPageDataRequestTransfer;

interface CmsStubInterface
{
    public function getFlattenedLocaleCmsPageData(
        FlattenedLocaleCmsPageDataRequestTransfer $flattenedLocaleCmsPageDataRequestTransfer
    ): FlattenedLocaleCmsPageDataRequestTransfer;
}
