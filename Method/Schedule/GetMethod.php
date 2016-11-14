<?php

namespace Vizzle\TaskBundle\Method\Schedule;

use Timiki\Bundle\RpcServerBundle\Mapping as RPC;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Vizzle\VizzleBundle\Method\AbstractMethod;

/**
 * @RPC\Method("schedule.get")
 */
class GetMethod extends AbstractMethod implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @Rpc\Param()
     */
    protected $paramName;

    /**
     * @Rpc\Execute()
     */
    public function execute()
    {
        return $this->serialize(
            $this
                ->container
                ->get('doctrine.orm.entity_manager')
                ->getRepository('VizzleTaskBundle:Schedule')
                ->findAll()
        );
    }
}