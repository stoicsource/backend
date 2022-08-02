<?php
namespace App\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;

final class RandomOrderFilter extends AbstractContextAwareFilter
{
    protected function filterProperty(string $property, $value, QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        if (strtolower($property) === 'order' && array_key_exists('random', $value)) {
            $queryBuilder->orderBy('RAND()');
        }
    }

    public function getDescription(string $resourceClass): array
    {
        return [];
    }
}