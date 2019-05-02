<?php
/**
 * Created by PhpStorm.
 * User: johan
 * Date: 2016-04-23
 * Time: 16:42
 */

namespace Tweakers\NestedSet;

use Tweakers\NestedSet\Adapter\AdapterInterface;

class Util
{

    /**
     * @param AdapterInterface $adapter
     * @param int $nodeId
     * @return array
     */
    public static function toArray(AdapterInterface $adapter, int $nodeId): array
    {
        $root = $adapter->getNode($nodeId);
        $nodes = [
            [
                'data' => $root->data,
                'left' => $root->lft,
                'right' => $root->rgt,
                'depth' => $root->depth,
            ]
        ];
        $n = $adapter->getAllChildren($nodeId);
        return array_merge($nodes, array_map(function (Node $n) {
            return [
                'data' => $n->data,
                'left' => $n->lft,
                'right' => $n->rgt,
                'depth' => $n->depth,
            ];
        }, $n));
    }

    /**
     * Check that the supplied node and its children are valid
     * @param AdapterInterface $adapter
     * @param int $nodeId
     * @return bool
     */
    public static function validate(AdapterInterface $adapter, int $nodeId): bool
    {
        $node = $adapter->getNode($nodeId);
        $positions = [$node->lft, $node->rgt];
        $children = $adapter->getAllChildren($nodeId);
        foreach ($children as $child) {
            $positions[] = $child->lft;
            $positions[] = $child->rgt;
        }

        sort($positions);

        // make sure the array is a sequence without holes
        for ($i = 1; $i < count($positions); $i++) {
            if ($positions[$i] != $positions[$i - 1] + 1) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param AdapterInterface $adapter
     * @param int $nodeId
     * @return string
     */
    public static function toString(AdapterInterface $adapter, int $nodeId): string
    {
        return ''; // TODO: dump to a nicely formatted string
    }

}
