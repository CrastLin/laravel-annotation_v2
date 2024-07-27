<?php
declare(strict_types=1);

namespace Crastlin\LaravelAnnotation\Extra;

/**
 * The method of creating a resource routing controller implementation
 * @Author crastlin@163.cm
 * @Date 2024-3-2
 */
interface ResourceInterface
{
    /**
     * Display homepage or list page or interface
     * Allow GET requests
     * @example route path: /resourcePath
     */
    function index();

    /**
     * Display new or edited pages
     * Allow GET requests
     * @example route path: /resourcePath/create
     */
    function create();

    /**
     * Save Data Interface
     * Allow POST requests
     * @example route path: /resourcePath
     */
    function store();

    /**
     * View data details page
     * Allow GET requests
     * @example route path: /resourcePath/{id}
     */
    function show(int|string $id);

    /**
     * Display editing page
     * Allow GET requests
     * @example route path: /resourcePath/{id}/edit
     */
    function edit(int|string $id);

    /**
     * Submit data update interface
     * Allow PUT/PATCH requests
     * @example route path: /resourcePath/{id}
     */
    function update(int|string $id);

    /**
     * Delete data interface
     * Allow DELETE requests
     * @example route path: /resourcePath/{id}
     */
    function destroy(int|string $id);
}
