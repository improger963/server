<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Site;

class SiteOwnershipMiddleware extends OwnershipMiddleware
{
    /**
     * Get the entity ID from the request
     *
     * @param Request $request
     * @return mixed
     */
    protected function getEntityId(Request $request)
    {
        return $request->route('site');
    }
    
    /**
     * Get the entity instance
     *
     * @param mixed $id
     * @return mixed
     */
    protected function getEntity($id)
    {
        return Site::find($id);
    }
    
    /**
     * Get the owner ID from the entity
     *
     * @param mixed $entity
     * @return int
     */
    protected function getOwnerId($entity)
    {
        return $entity->user_id;
    }
}