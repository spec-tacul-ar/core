<?php

use Illuminate\Support\Facades\Route;
use Spectacular\Core\Actions\Features\DeleteFeature;
use Spectacular\Core\Actions\Features\StoreFeature;
use Spectacular\Core\Actions\Features\UpdateFeature;
use Spectacular\Core\Actions\Projects\BrowseProjects;
use Spectacular\Core\Actions\Projects\DeleteProject;
use Spectacular\Core\Actions\Projects\OrganiseProject;
use Spectacular\Core\Actions\Projects\ReadProject;
use Spectacular\Core\Actions\Projects\StoreProject;
use Spectacular\Core\Actions\Projects\UpdateProject;
use Spectacular\Core\Actions\Requirements\CompleteRequirementTasks;
use Spectacular\Core\Actions\Requirements\DeleteRequirement;
use Spectacular\Core\Actions\Requirements\StoreRequirement;
use Spectacular\Core\Actions\Requirements\UpdateRequirement;
use Spectacular\Core\Actions\Tasks\DeleteTask;
use Spectacular\Core\Actions\Tasks\StoreTask;
use Spectacular\Core\Actions\Tasks\UpdateTask;
use Spectacular\Core\Actions\Unknowns\DeleteUnknown;
use Spectacular\Core\Actions\Unknowns\StoreUnknown;
use Spectacular\Core\Actions\Unknowns\UpdateUnknown;
use Spectacular\Core\Actions\Users\DeleteUser;
use Spectacular\Core\Actions\Users\StoreUser;
use Spectacular\Core\Actions\Users\UpdateUser;

Route::post('features/{feature}/edit', UpdateFeature::class);
Route::post('features/add', StoreFeature::class);
Route::post('features/{feature}/delete', DeleteFeature::class);

Route::get('projects/browse', BrowseProjects::class);
Route::post('projects/add', StoreProject::class);
Route::get('projects/{project}/read', ReadProject::class);
Route::post('projects/{project}/edit', UpdateProject::class);
Route::post('projects/{project}/delete', DeleteProject::class);
Route::post('projects/{project}/organise', OrganiseProject::class);

Route::post('unknowns/{unknown}/edit', UpdateUnknown::class);
Route::post('unknowns/add', StoreUnknown::class);
Route::post('unknowns/{unknown}/delete', DeleteUnknown::class);

Route::post('requirements/{requirement}/edit', UpdateRequirement::class);
Route::post('requirements/add', StoreRequirement::class);
Route::post('requirements/{requirement}/delete', DeleteRequirement::class);
Route::post('requirements/{requirement}/tasks/complete', CompleteRequirementTasks::class);

Route::post('tasks/{task}/edit', UpdateTask::class);
Route::post('tasks/add', StoreTask::class);
Route::post('tasks/{task}/delete', DeleteTask::class);

Route::post('users/add', StoreUser::class);
Route::post('users/{user}/edit', UpdateUser::class);
Route::post('users/{user}/delete', DeleteUser::class);
