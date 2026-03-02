<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Cms\Business\Version\Mapper;

use Generated\Shared\Transfer\CmsGlossaryTransfer;
use Generated\Shared\Transfer\CmsPageTransfer;
use Generated\Shared\Transfer\CmsTemplateTransfer;
use Generated\Shared\Transfer\CmsVersionDataTransfer;
use Generated\Shared\Transfer\CmsVersionTransfer;
use Orm\Zed\Cms\Persistence\SpyCmsPage;
use Orm\Zed\Cms\Persistence\SpyCmsVersion;

interface VersionDataMapperInterface
{
    public function mapToJsonData(CmsVersionDataTransfer $cmsVersionDataTransfer): string;

    public function mapToCmsVersionDataTransfer(SpyCmsPage $cmsPageEntity): CmsVersionDataTransfer;

    public function mapToCmsVersionTransfer(SpyCmsVersion $cmsVersionEntity): CmsVersionTransfer;

    public function mapToCmsTemplateData(SpyCmsPage $cmsPageEntity): CmsTemplateTransfer;

    public function mapToCmsPageLocalizedAttributesData(SpyCmsPage $cmsPageEntity): CmsPageTransfer;

    public function mapToCmsGlossaryKeyMappingsData(SpyCmsPage $cmsPageEntity): CmsGlossaryTransfer;
}
