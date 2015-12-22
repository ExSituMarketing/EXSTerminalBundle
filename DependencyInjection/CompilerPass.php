<?php

namespace EXS\TerminalBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class CompilerPass
 *
 * @package EXS\TerminalBundle\DependencyInjection
 */
class CompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('terminal.email.manager')) {
            return;
        }

        $definition = $container->getDefinition(
            'terminal.email.manager'
        );

        $transport = $container->get($definition->getArgument(2));

        $definition->replaceArgument(2, $transport);
    }
}
