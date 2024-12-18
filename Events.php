<?php

class Events
{

    private $username;

    const PUSH_EVENT   = "PushEvent";
    const ISSUES_EVENT = "IssuesEvent";
    const WATCH_EVENT  = "WatchEvent";
    const FORK_EVENT   = "ForkEvent";
    const CREATE_EVENT = "CreateEvent";

    public function __construct($username)
    {
        $this->username = $username;
    }

    public function getEvents()
    {
        $url = "https://api.github.com/users/{$this->username}/events";
        $result = $this->curlInitializ($url);
        return json_decode($result);
    }

    public function curlInitializ($url)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.103 Safari/537.36'
        ]);
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
            return null;
        }
        curl_close($ch);
        return $response;
    }

    static function formatResponse($response)
    {
        $events = $response;

        foreach ($events as $event) {
            $action = '';

            switch ($event->type) {
                case self::PUSH_EVENT:
                    $commit_count = count($event->payload->commits);
                    $action = "Pushed {$commit_count} commit(s) to {$event->repo->name}";
                    break;

                case self::ISSUES_EVENT:
                    $action = ucfirst($event->payload->action) . " an issue in {$event->repo->name}";
                    break;

                case self::WATCH_EVENT:
                    $action = "Starred {$event->repo->name}";
                    break;

                case self::FORK_EVENT:
                    $action = "Forked {$event->repo->name}";
                    break;

                case self::CREATE_EVENT:
                    $action = "Created {$event->payload->ref_type} in {$event->repo->name}";
                    break;

                default:
                    $eventType = str_replace("Event", "", $event->type);
                    $action = "{$eventType} in {$event->repo->name}";
                    break;
            }

            echo $action . "\n";
        }
    }
}
