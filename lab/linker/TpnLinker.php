<?php

namespace Fogio\Db;

class TpnLinker extends LinkerAbstract
{
    
    protected function link()
    {
        // n:1? 1:n? 1:1?
        $ref = "{$from}_id_{$to}{$toSuffix}";
        $dep = "{$to}_id_{$from}{$toSuffix}";
        $toLink = $fromLink = null;
        if ($fromModel->hasField($ref)) { // relation n:1 (ref)
            $toLink = $toModel->getKey();
            $fromLink = $ref;
        } elseif ($toModel->hasField($dep)) { // relation 1:n (dep)
            $toLink = $dep;
            $fromLink = $fromModel->getKey();
        } else { // 1:1
            $toLink = $toModel->getKey();
            $fromLink = $fromModel->getKey();
        }        
    }
    
}
