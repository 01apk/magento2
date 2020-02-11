<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Controller\Result;

use Magento\Theme\Controller\Result\JsFooterPlugin;
use Magento\Framework\App\Response\Http;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Unit test for Magento\Theme\Test\Unit\Controller\Result\JsFooterPlugin.
 */
class JsFooterPluginTest extends TestCase
{
    const STUB_XML_PATH_DEV_MOVE_JS_TO_BOTTOM = 'dev/js/move_script_to_bottom';

    /**
     * @var JsFooterPlugin
     */
    private $plugin;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var Http|MockObject
     */
    private $httpMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->setMethods(['isSetFlag'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->httpMock = $this->createMock(Http::class);

        $objectManager = new ObjectManagerHelper($this);
        $this->plugin = $objectManager->getObject(
            JsFooterPlugin::class,
            [
                'scopeConfig' => $this->scopeConfigMock
            ]
        );
    }

    /**
     * Data Provider for beforeSendResponse()
     *
     * @return array
     */
    public function sendResponseDataProvider(): array
    {
        return [
            [
                "content" => "<body><h1>Test Title</h1>" .
                    "<script text=\"text/javascript\">test</script>" .
                    "<script text=\"text/x-magento-template\">test</script>" .
                    "<p>Test Content</p></body>",
                "flag" => true,
                "result" => "<body><h1>Test Title</h1>" .
                    "<script text=\"text/x-magento-template\">test</script>" .
                    "<p>Test Content</p>" .
                    "<script text=\"text/javascript\">test</script>" .
                    "\n</body>"
            ],
            [
                "content" => "<body><p>Test Content</p></body>",
                "flag" => false,
                "result" => "<body><p>Test Content</p></body>"
            ],
            [
                "content" => "<body><p>Test Content</p></body>",
                "flag" => true,
                "result" => "<body><p>Test Content</p>\n</body>"
            ]
        ];
    }

    /**
     * Test beforeSendResponse
     *
     * @param string $content
     * @param bool $isSetFlag
     * @param string $result
     * @return void
     * @dataProvider sendResponseDataProvider
     */
    public function testBeforeSendResponse($content, $isSetFlag, $result): void
    {
        $this->httpMock->expects($this->once())
            ->method('getContent')
            ->willReturn($content);

        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(
                self::STUB_XML_PATH_DEV_MOVE_JS_TO_BOTTOM,
                ScopeInterface::SCOPE_STORE
            )
            ->willReturn($isSetFlag);

        $this->httpMock->expects($this->any())
            ->method('setContent')
            ->with($result);

        $this->plugin->beforeSendResponse($this->httpMock);
    }

    /**
     * Test BeforeSendResponse if content is not a string
     *
     * @return void
     */
    public function testBeforeSendResponseIfGetContentIsNotAString(): void
    {
        $this->httpMock->expects($this->once())
            ->method('getContent')
            ->willReturn([]);

        $this->scopeConfigMock->expects($this->never())
            ->method('isSetFlag')
            ->with(
                self::STUB_XML_PATH_DEV_MOVE_JS_TO_BOTTOM,
                ScopeInterface::SCOPE_STORE
            )
            ->willReturn(false);

        $this->plugin->beforeSendResponse($this->httpMock);
    }
}
