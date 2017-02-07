<?php
/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Unit\Spryker\Zed\Cms\Business\Page;

use Orm\Zed\Cms\Persistence\SpyCmsPage;
use Spryker\Zed\Cms\Business\Page\CmsPageActivator;
use Spryker\Zed\Cms\Dependency\Facade\CmsToTouchInterface;
use Spryker\Zed\Cms\Persistence\CmsQueryContainerInterface;
use Unit\Spryker\Zed\Cms\Business\CmsMocks;

/**
 * @group Unit
 * @group Spryker
 * @group Zed
 * @group Cms
 * @group Business
 * @group Page
 * @group CmsPageActivatorTest
 */
class CmsPageActivatorTest extends CmsMocks
{

    /**
     * @return void
     */
    public function testActivatePageShoulPersistActiveFlagAndTriggerTouch()
    {
        $cmsPageEntityMock = $this->createCmsPageEntityMock();
        $cmsPageEntityMock->expects($this->once())
            ->method('save');

        $touchFacadeMock = $this->createTouchFacadeMock();
        $touchFacadeMock->expects($this->once())
            ->method('touchActive');

        $cmsPageActivatorMock = $this->createCmsPageActivateMock($cmsPageEntityMock, null, $touchFacadeMock);

        $cmsPageActivatorMock->activate(1);

        $this->assertTrue($cmsPageEntityMock->getIsActive());
    }

    /**
     * @return void
     */
    public function testDeActivatePageShoulPersistInActiveFlagAndTriggerTouch()
    {
        $cmsPageEntityMock = $this->createCmsPageEntityMock();
        $cmsPageEntityMock->expects($this->once())
            ->method('save');

        $touchFacadeMock = $this->createTouchFacadeMock();
        $touchFacadeMock->expects($this->once())
            ->method('touchActive');

        $cmsPageActivatorMock = $this->createCmsPageActivateMock($cmsPageEntityMock, null, $touchFacadeMock);

        $cmsPageActivatorMock->deactivate(1);

        $this->assertFalse($cmsPageEntityMock->getIsActive());
    }

    /**
     * @param \Orm\Zed\Cms\Persistence\SpyCmsPage $cmsPageEntity
     * @param \Spryker\Zed\Cms\Persistence\CmsQueryContainerInterface|null $cmsQueryContainerMock
     * @param \Spryker\Zed\Cms\Dependency\Facade\CmsToTouchInterface|null $touchFacadeMock
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\Spryker\Zed\Cms\Business\Page\CmsPageActivator
     */
    protected function createCmsPageActivateMock(
        SpyCmsPage $cmsPageEntity,
        CmsQueryContainerInterface $cmsQueryContainerMock = null,
        CmsToTouchInterface $touchFacadeMock = null
    ) {

        if ($cmsQueryContainerMock === null) {
            $cmsQueryContainerMock = $this->createCmsQueryContainerMock();
        }

        if ($touchFacadeMock === null) {
            $touchFacadeMock = $this->createTouchFacadeMock();
        }

        $cmsPageActivatorMock = $this->getMockBuilder(CmsPageActivator::class)
            ->setMethods(['getCmsPageEntity'])
            ->setConstructorArgs([$cmsQueryContainerMock, $touchFacadeMock])
            ->getMock();

        $cmsPageActivatorMock->method('getCmsPageEntity')
            ->willReturn($cmsPageEntity);

        return $cmsPageActivatorMock;

    }

}
