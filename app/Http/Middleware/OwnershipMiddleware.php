<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

abstract class OwnershipMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get the authenticated user
        $user = Auth::user();
        
        // Get the entity ID from the route
        $entityId = $this->getEntityId($request);
        
        // Get the entity instance
        $entity = $this->getEntity($entityId);
        
        // Check if the entity belongs to the user
        if ($entity && $this->getOwnerId($entity) !== $user->id) {
            return response()->json(['error' => 'Unauthorized access to resource'], 403);
        }
        
        return $next($request);
    }
    
    /**
     * Get the entity ID from the request
     *
     * @param Request $request
     * @return mixed
     */
    abstract protected function getEntityId(Request $request);
    
    /**
     * Get the entity instance
     *
     * @param mixed $id
     * @return mixed
     */
    abstract protected function getEntity($id);
    
    /**
     * Get the owner ID from the entity
     *
     * @param mixed $entity
     * @return int
     */
    abstract protected function getOwnerId($entity);
}