<?php

namespace App;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateCommand extends Command
{


    protected static $defaultName = 'app:generate-test-data';

    const NUMBER_OF_USERS = 20;

    const NUMBER_OF_ISSUES = 1000;

    protected function configure()
    {
        $this->setDescription("Generates test data for https://my-json-server.typicode.com/");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fakerFactory = \Faker\Factory::create();

        $users = [];

        for ($i = 1; $i <= self::NUMBER_OF_USERS; $i++) {
            $users[] = (object)[
              'id' => $i,
              'name' => $fakerFactory->name(),
              'email' => $fakerFactory->email(),
            ];
        }

        $components = array_map(function ($i) {
            return (object)$i;
        },
          [
            ['id' => 1, 'DEVOPS'],
            ['id' => 2, 'DESIGN'],
            ['id' => 3, 'BACKEND'],
            ['id' => 4, 'FRONTEND'],
          ]
        );

        $issues = [];

        for ($i = 1; $i <= self::NUMBER_OF_ISSUES; $i++) {
            $issueComponents = array_filter($components, function ($component) {
                return rand(0, 100) < 50 ? true : false;
            });
            $issues[] = (object)[
              'id' => $i,
              'code' => $fakerFactory->slug(),
              'components' => array_reduce($issueComponents, function ($c, $i) {
                  return array_merge($c, [$i->id]);
              }, []),
            ];
        }

        $timelogs = [];

        $logId = 1;
        foreach ($issues as $issue) {
            $numberTimelogs = rand(1, 5);
            for ($i = 0; $i < $numberTimelogs; $i++) {
                $timelogs[] = (object)[
                  'id' => $logId++,
                  'issue_id' => $issue->id,
                  'user_id' => $users[array_rand($users)]->id,
                  'seconds_logged' => rand(1, 60 * 60 * 8),
                ];
            }
        }

        file_put_contents("./db.json", json_encode([
          'users' => $users,
          'components' => $components,
          'issues' => $issues,
          'timelogs' => $timelogs,
        ]));

    }

}
