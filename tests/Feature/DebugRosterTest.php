<?php

use Illuminate\Support\Facades\Session;

it('debugs assignment flow', function () {
    // Clear session at start
    Session::flush();

    // First get the initial page to see what's there
    $response = $this->get(route('roster.index'));
    $response->assertStatus(200);

    // Check what the title is initially - should be "Semana sin título" for user-facing display
    $content = $response->getContent();
    expect($content)->toContain('<h1>Semana sin título</h1>');

    // Let's manually check what title() returns in the controller
    $this->post(route('roster.assign'), [
        'day' => 'lunes',
        'shift' => 'manana',
        'employee_id' => 1,
    ])->assertRedirect(route('roster.index'));

    // Now check that the assignment is actually in the session
    $roster = Session::get('roster');
    expect($roster)->toHaveKey('lunes');
    expect($roster['lunes']['manana'])->toBe(1);

    // Get the page again after assignment
    $response2 = $this->get(route('roster.index'));
    $content2 = $response2->getContent();

    // Check what's in the title now - it should be "Semana sin título" for user-facing display
    expect($content2)->toContain('<h1>Semana sin título</h1>');

    // But let's also check if employee name shows up
    expect($content2)->toContain('Marta Ferrer');
});