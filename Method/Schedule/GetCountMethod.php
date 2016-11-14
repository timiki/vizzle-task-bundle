<?php

namespace Vizzle\TaskBundle\Method\Schedule;

use Timiki\Bundle\RpcServerBundle\Mapping as RPC;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * @RPC\Method("schedule.getCount")
 */
class GetCountMethod implements ContainerAwareInterface
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
        $em = $this->container->get('doctrine.orm.entity_manager');

        $qb = $em->getRepository('VizzleTaskBundle:Schedule')->createQueryBuilder('schedule');
        $qb->select($qb->expr()->count('schedule.id'));

        return $qb->getQuery()->getSingleScalarResult();
    }
}