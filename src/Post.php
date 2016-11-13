<?php

namespace \Fogio\Db;

class Post extends TableAbstract 
{

    public function getName() 
    {
        return 'post';
    }

    public function getKey() 
    {
        return 'post_id';
    }

    public function getFields() 
    {
        return [
            'post_id',
            'post_id_user',
            'post_id_comment_first',
            'post_id_comment_last',
            'post_title',
        ];
    }

    public function  getLinks() 
    {
        return [
            (new Link())
                ->setName('user')
                ->setField('post_id_user')
                ->setForeign('user', 'user_id'),
            (new Link())
                ->setName('comment:last')
                ->setField('post_id_comment_last')
                ->setForeign('comment', 'comment_id'),
            (new Link())
                ->setName('comment:last')
                ->setForeign('comment', 'comment_id')
                ->setWhere()
                ->setAlias()
                ->setJoin(Link::JOIN_INNER),
                
        ];
    }
    
    public function onFdq($fdq)
    {
        return $fdq;
    }
    
    public function onResult($row)
    {
        return $row;
    }
    
    public function onInsert($row)
    {
        return $row;
    }
    
    public function onUpdate($data, $fdq)
    {
        
    }
    
    public function onDelete($data, $fdq)
    {
        
    }
    
    public function onWrite($data, $fdq)
    {
        
    }
}
