<?php

namespace WebArch\BitrixEmailAsLogin\Test;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WebArch\BitrixEmailAsLogin\EventHandlers;

class EventHandlersTest extends TestCase
{
    /**
     * @var EventHandlers
     */
    private $eventHandlers;

    protected function setUp()
    {
        $this->eventHandlers = $this->getMockBuilder(EventHandlers::class)
                                    ->setMethods(['checkEmail', 'throwEmailNotFoundException'])
                                    ->getMock();
        $this->eventHandlers->method('throwEmailNotFoundException')
                            ->willThrowException(new InvalidArgumentException(''));

        $this->eventHandlers->method('checkEmail')
                            ->willReturnCallback(
                                ['\WebArch\BitrixEmailAsLogin\Test\Fixture\BitrixFixture', 'checkEmail']
                            );

    }

    /**
     * @dataProvider emailLoginDataProvider
     *
     * @param array $fields
     * @param string $expectedEmailAndLogin
     */
    public function testEmailAsLogin(
        array $fields,
        string $expectedEmailAndLogin = null
    ) {
        $fieldsCopy = $fields;

        $this->eventHandlers->makeEmailAsLogin($fieldsCopy);

        $actualEmail = null;
        if (array_key_exists('EMAIL', $fieldsCopy)) {
            $actualEmail = $fieldsCopy['EMAIL'];
        }

        $actualLogin = null;
        if (array_key_exists('LOGIN', $fieldsCopy)) {
            $actualLogin = $fieldsCopy['LOGIN'];
        }

        $this->assertEquals($expectedEmailAndLogin, $actualEmail);
        $this->assertEquals($expectedEmailAndLogin, $actualLogin);
    }

    public function testMissingEmailAndLogin()
    {
        $fields = ['EMAIL' => 'foo', 'LOGIN' => 'bar'];
        $this->expectException(InvalidArgumentException::class);
        $this->eventHandlers->makeEmailAsLogin($fields);
    }

    /**
     * @return array
     */
    public function emailLoginDataProvider(): array
    {
        return [
            'External registration' => [
                /**
                 *
                 */
                [
                    'EXTERNAL_AUTH_ID' => 'foo',
                ],
                null,
            ],
            'Missing fields'        => [

                [
                ],
                null,
            ],
            'No email, but login'   => [

                [
                    'LOGIN' => 'chuck@example.com',
                ],
                'chuck@example.com',
            ],
            'No login, but email'   => [

                [
                    'EMAIL' => 'chuck@example.com',
                ],
                'chuck@example.com',
            ],
            'Two emails'            => [

                [
                    'EMAIL' => 'chuck@example.com',
                    'LOGIN' => 'other_chuck@example.com',
                ],
                'chuck@example.com',
            ],
            'Email in login'        => [

                [
                    'EMAIL' => 'foo',
                    'LOGIN' => 'chuck@example.com',
                ],
                'chuck@example.com',
            ],
            'Email in email'        => [

                [
                    'EMAIL' => 'chuck@example.com',
                    'LOGIN' => 'bar',
                ],
                'chuck@example.com',
            ],

        ];
    }
}
