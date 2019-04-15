<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class APITest extends TestCase
{

   protected $db;

   const DB_URL = "https://raw.githubusercontent.com/bomoko/algm_assessment/master/db.json";

   public function setUp(): void
   {
       parent::setUp();
       $this->db = json_decode(file_get_contents(self::DB_URL));
   }

   /**
    * The endpoint under test should return JSON of an array of objects containing users ids
    * and their total time
    * [
    *   {'user_id': <int>, 'seconds_logged': <int>},
    *   ... other users ...
    * ]
    *
    * @test
    */
   public function it_should_provide_sum_of_all_users_time()
   {
       //test a random user
       $user = $this->db->users[array_rand($this->db->users)];
       $totalSecondsLogged = array_reduce($this->db->timelogs,
         function ($c, $i) use ($user) {
             return $c + ($i->user_id == $user->id ? $i->seconds_logged : 0);
         }, 0);

       $response = $this->json('GET', '/user-timelogs');

       $response->assertJsonFragment([
         'user_id' => $user->id,
         'seconds_logged' => $totalSecondsLogged,
       ]);
   }

   /**
    * The endpoint under test should return JSON of an array of objects containing
    * the number of issues per component, and the total number of seconds
    * logged to that component
    *
    * Schematically :-
    *
    * [
    *   {'component_id': <int>, 'number_of_issues': <int>, 'seconds_logged': <int>},
    *   ... other users ...
    * ]*
    *
    * @test
    */
   public function it_should_return_component_metadata()
   {
       $component = $this->db->components[array_rand($this->db->components)];
       $componentIssueIds = array_reduce($this->db->issues, function ($c, $i) use ($component) {
          if(in_array($component->id, $i->components)) {
              return array_merge($c, [$i->id]);
          }
          return $c;
       }, []);

       $totalSecondsLogged = array_reduce($this->db->timelogs,
         function ($c, $i) use ($componentIssueIds) {
             return $c + (in_array($i->issue_id, $componentIssueIds) ? $i->seconds_logged : 0);
         }, 0);

       $response = $this->json('GET', '/component-metadata');

       $response->assertJsonFragment([
         'component_id' => $component->id,
         'number_of_issues' => count($componentIssueIds),
         'seconds_logged' => $totalSecondsLogged,
       ]);
   }

}

