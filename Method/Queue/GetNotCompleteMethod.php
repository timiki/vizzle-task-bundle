<?php

namespace Vizzle\TaskBundle\Method\Queue;

use Timiki\Bundle\RpcServerBundle\Mapping as RPC;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Vizzle\VizzleBundle\Method\AbstractMethod;

/**
 * @RPC\Method("queue.getNotComplete")
 */
class GetNotCompleteMethod extends AbstractMethod implements ContainerAwareInterface
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

        return $this->serialize(
            $qb
                ->getQuery()
                ->execute()
        );
    }
}