<?php

namespace Vizzle\TaskBundle\Method\Queue;

use Timiki\Bundle\RpcServerBundle\Mapping as RPC;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Vizzle\VizzleBundle\Method\AbstractMethod;

/**
 * @RPC\Method("queue.getCount")
 */
class GetCountMethod extends AbstractMethod implements ContainerAwareInterface
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

        $qb = $em->getRepository('VizzleTaskBundle:Queue')->createQueryBuilder('queue');
        $qb->andWhere('queue.status <> :status');
        $qb->setParameter('status', 'COMPLETE');
        $qb->select($qb->expr()->count('queue.id'));

        return $qb->getQuery()->getSingleScalarResult();
    }
}