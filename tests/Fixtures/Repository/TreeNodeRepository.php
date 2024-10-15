<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\Fixtures\Repository;

use Doctrine\ORM\EntityRepository;
use Knp\DoctrineBehaviors\ORM\Tree\TreeTrait;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\TreeNodeEntity;

/**
 * @extends EntityRepository<TreeNodeEntity>
 */
final class TreeNodeRepository extends EntityRepository
{
    use TreeTrait;
}
