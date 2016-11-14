<?php

namespace Vizzle\TaskBundle\Method\Queue\Stat;

use Timiki\Bundle\RpcServerBundle\Mapping as RPC;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Vizzle\VizzleBundle\Method\AbstractMethod;

/**
 * @RPC\Method("queue.stat")
 */
class StatMethod extends AbstractMethod implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @Rpc\Execute()
     */
    public function execute()
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $qb = $em->getRepository('VizzleTaskBundle:Stat\TaskQueue')->createQueryBuilder('stat');

        $qb->where('stat.date > :date');
        $qb->setParameter('date', (new \DateTime())->sub(new \DateInterval('PT30M')));
        $qb->orderBy('stat.date', 'asc');

        return $this->serialize(
            $qb->getQuery()->execute()
        );
    }
}