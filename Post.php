<?php


use Fogio\Db\Table\Table;
use Fogio\Db\Table\Extension\Vcs;

class Post extends Table 
{

    protected function provideName() 
    {
        return 'post';
    }

    protected function provideKey() 
    {
        return 'post_id';
    }

    protected function provideFields() 
    {
        return [
            'post_id',
            'post_id_user',
            'post_id_comment_first',
            'post_id_comment_last',
            'post_title',
        ];
    }

    protected function provideExtensions()
    {
        return [
           (new DefaultOrder)->setOrder('post_id DESC'),
           (new SerializeFields)->setFields(['post_attr']),
           new Vcs(),
        ];
        
    }
    
    protected function provideLinks() 
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
    
}
