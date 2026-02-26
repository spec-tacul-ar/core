<?php

use Illuminate\Support\Facades\Route;
use Spectacular\Core\Actions\FeatureDelete;
use Spectacular\Core\Actions\FeatureStore;
use Spectacular\Core\Actions\FeatureUpdate;
use Spectacular\Core\Actions\ProjectBrowse;
use Spectacular\Core\Actions\ProjectDelete;
use Spectacular\Core\Actions\ProjectOrganise;
use Spectacular\Core\Actions\ProjectRead;
use Spectacular\Core\Actions\ProjectStore;
use Spectacular\Core\Actions\ProjectUpdate;
use Spectacular\Core\Actions\RequirementCompleteTasks;
use Spectacular\Core\Actions\RequirementDelete;
use Spectacular\Core\Actions\RequirementStore;
use Spectacular\Core\Actions\RequirementUpdate;
use Spectacular\Core\Actions\TaskDelete;
use Spectacular\Core\Actions\TaskStore;
use Spectacular\Core\Actions\TaskUpdate;
use Spectacular\Core\Actions\UnknownDelete;
use Spectacular\Core\Actions\UnknownStore;
use Spectacular\Core\Actions\UnknownUpdate;
use Spectacular\Core\Actions\UserDelete;
use Spectacular\Core\Actions\UserStore;
use Spectacular\Core\Actions\UserUpdate;

Route::post('features/{feature}/edit', FeatureUpdate::class);
Route::post('features/add', FeatureStore::class);
Route::post('features/{feature}/delete', FeatureDelete::class);

 Route::get('projects/browse', ProjectBrowse::class);
Route::post('projects/add', ProjectStore::class);
 Route::get('projects/{project}/read', ProjectRead::class);
Route::post('projects/{project}/edit', ProjectUpdate::class);
Route::post('projects/{project}/delete', ProjectDelete::class);
Route::post('projects/{project}/organise', ProjectOrganise::class);

Route::post('unknowns/{unknown}/edit', UnknownUpdate::class);
Route::post('unknowns/add', UnknownStore::class);
Route::post('unknowns/{unknown}/delete', UnknownDelete::class);

Route::post('requirements/{requirement}/edit', RequirementUpdate::class);
Route::post('requirements/add', RequirementStore::class);
Route::post('requirements/{requirement}/delete', RequirementDelete::class);
Route::post('requirements/{requirement}/tasks/complete', RequirementCompleteTasks::class);

Route::post('tasks/{task}/edit', TaskUpdate::class);
Route::post('tasks/add', TaskStore::class);
Route::post('tasks/{task}/delete', TaskDelete::class);

Route::post('users/add', UserStore::class);
Route::post('users/{user}/edit', UserUpdate::class);
Route::post('users/{user}/delete', UserDelete::class);
