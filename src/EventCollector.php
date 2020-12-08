<?php
/*
 * Copyright 2020 Beijing Volcano Engine Technology Co., Ltd.
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except in compliance with the License. You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software distributed under the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the License for the specific language governing permissions and limitations under the License.
 */

namespace DataRangers;

use DataRangers\Model\Event;
use DataRangers\Model\Header;
use DataRangers\Model\Message;
use DataRangers\Model\ProfileMethod;

class EventCollector implements Collector
{
    public $consumer;
    public $appType;

    /**
     * EventCollector constructor.
     * @param $consumer
     */
    public function __construct($consumer, $appType)
    {
        $this->consumer = $consumer;
        $this->appType = $appType;
    }

    public function sendEvent($userUniqueId, $appId, $custom, $eventName, $eventParams)
    {
        $header = new Header();
        $header->setAppId($appId);
        $header->setUserUniqueId($userUniqueId);
        if ($custom != null) $header->setCustom($custom);
        $events = [];
        if (is_array($eventName) && is_array($eventParams)) {
            $events = array_map(function ($event_name, $event_params) use ($userUniqueId) {
                $event = new Event($userUniqueId);
                $event->setEvent($event_name);
                $event->setParams($event_params);
                return $event;
            }, $eventName, $eventParams);
        } else {
            $event = new Event($userUniqueId);
            $event->setEvent($eventName);
            $event->setParams($eventParams);
            $events[] = $event;
        }
        $message = new Message();
        $message->setUserUniqueId($userUniqueId);
        $message->setEventV3($events);
        $message->setAppId($appId);
        $message->setAppType($this->appType);
        $message->setHeader($header);
        $this->consumer->send($message);
    }

    public function profile($userUniqueId, $appId, $eventName, $eventParams)
    {
        $header = new Header();
        $header->setAppId($appId);
        $header->setUserUniqueId($userUniqueId);
        $events = [];
        $event = new Event($userUniqueId);
        $event->setEvent($eventName);
        $event->setParams($eventParams);
        $events[] = $event;
        $message = new Message();
        $message->setUserUniqueId($userUniqueId);
        $message->setEventV3($events);
        $message->setAppId($appId);
        $message->setAppType($this->appType);
        $message->setHeader($header);
        $this->consumer->send($message);
    }

    public function profileSet($userUniqueId, $appId, $eventParams)
    {
        $this->profile($userUniqueId, $appId, ProfileMethod::SET, $eventParams);
    }


    public function __destruct()
    {
        if ($this->consumer != null) $this->consumer->close();
    }

    public function profileUnset($userUniqueId, $appId, $eventParams)
    {
        $this->profile($userUniqueId, $appId, ProfileMethod::UN_SET, $eventParams);
    }

    public function profileSetOnce($userUniqueId, $appId, $eventParams)
    {
        $this->profile($userUniqueId, $appId, ProfileMethod::SET_ONCE, $eventParams);
    }

    public function profileIncrement($userUniqueId, $appId, $eventParams)
    {
        $this->profile($userUniqueId, $appId, ProfileMethod::INCREMENT, $eventParams);
    }

    public function profileAppend($userUniqueId, $appId, $eventParams)
    {
        $this->profile($userUniqueId, $appId, ProfileMethod::APPEND, $eventParams);
    }
}