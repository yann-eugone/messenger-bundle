<?php

namespace Yokai\MessengerBundle\Tests\Helper;

use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Translation\TranslatorInterface;
use Twig\Environment;
use Yokai\MessengerBundle\Helper\ContentBuilder;

/**
 * @author Yann Eugoné <eugone.yann@gmail.com>
 */
class ContentBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectProphecy
     */
    private $twig;

    /**
     * @var ObjectProphecy
     */
    private $translator;

    protected function setUp()
    {
        $this->twig = $this->prophesize(Environment::class);
        $this->translator = $this->prophesize(TranslatorInterface::class);
    }

    protected function tearDown()
    {
        unset(
            $this->twig,
            $this->translator
        );
    }

    protected function createHelper(array $defaults)
    {
        return new ContentBuilder(
            $this->translator->reveal(),
            $defaults,
            null,
            $this->twig->reveal()
        );
    }

    /**
     * @expectedException \Yokai\MessengerBundle\Exception\BadMethodCallException
     */
    public function testConfigureShouldBeCalledBeforeGetBody()
    {
        $helper = $this->createHelper([]);

        $helper->getBody([]);
    }

    /**
     * @expectedException \Yokai\MessengerBundle\Exception\BadMethodCallException
     */
    public function testConfigureShouldBeCalledBeforeGetSubject()
    {
        $helper = $this->createHelper([]);

        $helper->getSubject([]);
    }

    public function testPassingUnknownOptionsIsNotThrowingAnException()
    {
        $helper = $this->createHelper([]);

        $helper->configure([
            'subject' => 'subject',
            'template' => 'template',
            'translation_catalog' => 'messages',
            'option_that_do_not_exists' => 'unknown',
        ]);

        $this->assertTrue(true); // if we are here, any exception was throwed
    }

    public function testAffectingDefaults()
    {
        $helper = $this->createHelper([
            'subject' => 'subject',
            'template' => 'template',
            'translation_catalog' => 'messages',
            'option_that_do_not_exists' => 'unknown',
        ]);

        $helper->configure([]);

        $this->assertTrue(true); // if we are here, any exception was throwed
    }

    /**
     * @dataProvider subjectProvider
     */
    public function testBuildingSubject(array $options, array $parameters, $expectedSubject, array $expectedParameters)
    {
        $helper = $this->createHelper([
            'template' => 'template',
            'translation_catalog' => 'messages',
        ]);

        $helper->configure($options);

        $this->translator->trans($expectedSubject, $expectedParameters, 'messages')
            ->shouldBeCalled()
            ->willReturn('test ok');

        $this->assertSame('test ok', $helper->getSubject($parameters));
    }

    /**
     * @dataProvider bodyProvider
     */
    public function testBuildingBody(array $options, array $parameters, $expectedTemplate, $expectedParameters)
    {
        $helper = $this->createHelper([
            'subject' => 'subject',
            'translation_catalog' => 'messages',
        ]);

        $helper->configure($options);

        $this->twig->render($expectedTemplate, $expectedParameters)
            ->shouldBeCalled()
            ->willReturn('test ok');

        $this->assertSame('test ok', $helper->getBody($parameters));
    }

    /**
     * @dataProvider noBuild
     */
    public function testBuildNoSubject($value)
    {
        $helper = $this->createHelper([]);

        $helper->configure(['subject' => $value, 'template' => $value]);

        $this->twig->render(Argument::cetera())
            ->shouldNotBeCalled();
        $this->translator->trans(Argument::cetera())
            ->shouldNotBeCalled();

        $this->assertSame('', $helper->getSubject([]));
        $this->assertSame('', $helper->getBody([]));
    }

    public function subjectProvider()
    {
        return [
            [
                [
                    'subject' => 'Welcome !',
                    'subject_parameters' => [],
                ],
                [
                ],
                'Welcome !',
                [
                ]
            ],
            [
                [
                    'subject' => 'Welcome %name% !',
                    'subject_parameters' => ['%name%'],
                ],
                [
                    '%name%' => 'John',
                ],
                'Welcome %name% !',
                [
                    '%name%' => 'John',
                ]
            ],
            [
                [
                    'subject' => 'Welcome %name% !',
                    'subject_parameters' => ['%name%'],
                ],
                [
                    '%name%' => 'John',
                    '%last_name%' => 'Doe',
                ],
                'Welcome %name% !',
                [
                    '%name%' => 'John',
                ]
            ],
        ];
    }

    public function bodyProvider()
    {
        return [
            [
                [
                    'template' => ':hello:world.txt.twig',
                ],
                [
                ],
                ':hello:world.txt.twig',
                [
                ],
            ],
            [
                [
                    'template' => ':hello:name.txt.twig',
                    'template_vars' => [
                        'date' => '2015-11-12',
                    ],
                ],
                [
                    'name' => 'John Doe',
                ],
                ':hello:name.txt.twig',
                [
                    'name' => 'John Doe',
                    'date' => '2015-11-12',
                ],
            ],
            [
                [
                    'template' => ':hello:{greet}.txt.twig',
                    'template_parameters' => ['{greet}'],
                    'template_vars' => [
                        'date' => '2015-11-12',
                    ],
                ],
                [
                    'name' => 'John Doe',
                    '{greet}' => 'name',
                ],
                ':hello:name.txt.twig',
                [
                    'name' => 'John Doe',
                    'date' => '2015-11-12',
                    '{greet}' => 'name',
                ],
            ],
        ];
    }

    public function noBuild()
    {
        return [
            [null],
            [''],
            [false],
        ];
    }
}
