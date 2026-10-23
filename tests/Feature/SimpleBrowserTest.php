<?php

use Illuminate\Support\Facades\Session;

it('tests simple browser flow', function () {
    // Clear session at start
    Session::flush();

    // Get the initial roster page (should be empty)
    $response = $this->get(route('roster.index'));
    $response->assertStatus(200);

    // Check that we have the expected title in the response
    $content = $response->getContent();
    expect($content)->toContain('Semana sin título');

    // Check for empty cells
    expect($content)->toContain('class="vacia">—');
});