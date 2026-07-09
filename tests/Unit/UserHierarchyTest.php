<?php

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// --- getTeamUserIds ---

test('getTeamUserIds includes only self when there are no reports or teams', function () {
    $user = User::factory()->create();

    expect($user->getTeamUserIds())->toBe([$user->id]);
});

test('getTeamUserIds includes direct reports via sales_manager_id', function () {
    $manager = User::factory()->create();
    $report = User::factory()->create(['sales_manager_id' => $manager->id]);

    expect($manager->getTeamUserIds())->toEqualCanonicalizing([$manager->id, $report->id]);
});

test('getTeamUserIds recurses through multiple levels of direct reports', function () {
    $topManager = User::factory()->create();
    $midManager = User::factory()->create(['sales_manager_id' => $topManager->id]);
    $report = User::factory()->create(['sales_manager_id' => $midManager->id]);

    expect($topManager->getTeamUserIds())->toEqualCanonicalizing([
        $topManager->id, $midManager->id, $report->id,
    ]);
});

test('getTeamUserIds includes team members when user manages a team', function () {
    $manager = User::factory()->create();
    $team = Team::create(['name' => 'Alpha', 'team_manager_id' => $manager->id]);
    $member = User::factory()->create(['team_id' => $team->id]);

    expect($manager->getTeamUserIds())->toEqualCanonicalizing([$manager->id, $member->id]);
});

test('getTeamUserIds does not duplicate a user reachable via both manager and team paths', function () {
    $manager = User::factory()->create();
    $team = Team::create(['name' => 'Alpha', 'team_manager_id' => $manager->id]);
    $member = User::factory()->create(['sales_manager_id' => $manager->id, 'team_id' => $team->id]);

    $ids = $manager->getTeamUserIds();

    expect($ids)->toEqualCanonicalizing([$manager->id, $member->id])
        ->and(array_count_values($ids)[$member->id])->toBe(1);
});

// --- getAncestorUserIds ---

test('getAncestorUserIds is empty when user has no manager or team', function () {
    $user = User::factory()->create();

    expect($user->getAncestorUserIds())->toBe([]);
});

test('getAncestorUserIds walks up through sales_manager_id', function () {
    $topManager = User::factory()->create();
    $midManager = User::factory()->create(['sales_manager_id' => $topManager->id]);
    $report = User::factory()->create(['sales_manager_id' => $midManager->id]);

    expect($report->getAncestorUserIds())->toBe([$midManager->id, $topManager->id]);
});

test('getAncestorUserIds falls back to team manager when sales_manager_id is not set', function () {
    $teamManager = User::factory()->create();
    $team = Team::create(['name' => 'Alpha', 'team_manager_id' => $teamManager->id]);
    $member = User::factory()->create(['team_id' => $team->id]);

    expect($member->getAncestorUserIds())->toBe([$teamManager->id]);
});

test('getAncestorUserIds does not treat a self-managed team as an ancestor', function () {
    $manager = User::factory()->create();
    $team = Team::create(['name' => 'Alpha', 'team_manager_id' => $manager->id]);
    $manager->update(['team_id' => $team->id]);

    expect($manager->fresh()->getAncestorUserIds())->toBe([]);
});

test('getAncestorUserIds stops instead of looping forever when managers form a cycle', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create(['sales_manager_id' => $userA->id]);
    $userA->update(['sales_manager_id' => $userB->id]);

    // A -> B -> A: walks both hops once, then the cycle guard (in_array check)
    // stops it from looping a third time back to B.
    expect($userA->fresh()->getAncestorUserIds())->toBe([$userB->id, $userA->id]);
});
