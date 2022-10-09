<?php

namespace RedFreak\ModularEnv\Tests;

use Orchestra\Testbench\TestCase as PackageTestCase;
use RedFreak\ModularEnv\Foundation\Bootstrap\LoadEnvironmentVariables as RedFreakLoadEnvironmentVariables;
use RedFreak\ModularEnv\ModularEnvServiceProvider;
use ReflectionClass;
use ReflectionException;

class TestCase extends PackageTestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return [
            ModularEnvServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        //
    }

    /**
     * Scans the file for the use of facades, which is forbidden in this context (config is not loaded, so the facades
     * do not work.
     *
     * @param class-string $classToCheck
     *
     * @throws ReflectionException
     */
    protected function checkForFacadeInheritance(string $classToCheck): void
    {
        $useStatements = [];

        $reflection = new ReflectionClass($classToCheck);
        $tokens = token_get_all(file_get_contents($reflection->getFileName()));

        $tokenCount   = count($tokens);
        $currentToken = 0;
        do {
            if (!is_array($tokens[$currentToken])) {
                ++$currentToken;
                continue;
            }
            // if there is a T_USE-Token, we look at the FQN
            if (token_name($tokens[$currentToken][0]) === 'T_USE') {
                $this->checkClassForFacadeInheritance($tokens[$currentToken + 2][1], $tokens[$currentToken + 2][2]);

                // remember the use-statements
                if (token_name(data_get($tokens, ($currentToken + 4) . '.0')) === 'T_AS') {
                    $useStatements[] = data_get($tokens, ($currentToken + 5) . '.1');
                    $currentToken += 5;
                } else {
                    $useStatements[] = basename($tokens[$currentToken + 2][1]);
                    $currentToken += 3;
                }
            }

            if (!is_array($tokens[$currentToken])
                || in_array($tokens[$currentToken][0], [T_COMMENT, T_DOC_COMMENT, T_COMMENT, T_START_HEREDOC], true)
            ) {
                ++$currentToken;
                continue;
            }

            if ($currentToken + 1 < $tokenCount
                && is_array($tokens[$currentToken + 1]) && token_name($tokens[$currentToken+1][0]) === 'T_DOUBLE_COLON'
                && (token_name($tokens[$currentToken][0]) === 'T_NAME_FULLY_QUALIFIED' || token_name($tokens[$currentToken][0]) === 'T_STRING')
            ) {
                $className = $tokens[$currentToken][1];
                // is the className part of the use-statements, we checked the class
                if (in_array($className, $useStatements)) {
                    ++$currentToken;
                    continue;
                }

                $this->checkClassForFacadeInheritance($className, $tokens[$currentToken][2]);
            }

            ++$currentToken;
        } while ($currentToken < $tokenCount);
    }

    /**
     * @param  string  $className
     * @param  int  $line
     *
     * @return void
     * @throws ReflectionException
     */
    private function checkClassForFacadeInheritance(string $className, int $line): void
    {
        $this->assertDoesNotMatchRegularExpression(
            '/\\\\Facades\\\\/',
            $className,
            'The use of Facades is not allowed in this context ('.$line.': '.$className.').'
        );
        // check also the parent classes
        $reflectionOfUsedClass = new ReflectionClass($className);
        while ($reflectionOfUsedClass = $reflectionOfUsedClass->getParentClass()) {
            $this->assertDoesNotMatchRegularExpression(
                '/\\\\Facade/',
                $reflectionOfUsedClass->getName(),
                'The use of Facades is not allowed in this context ('.$line.': '.$className.').'
            );
        }
    }
}
