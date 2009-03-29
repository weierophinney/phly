<?php

class Phly_Mvc_PubSubProvider extends Phly_PubSub_Provider
{
    /**
     * @var string last published topic
     */
    protected $_lastTopic;

    /**
     * Publish topic
     * 
     * @param  string $topic 
     * @param  null|array $args 
     * @return void
     */
    public function publish($topic, $args = null)
    {
        if (empty($this->_topics[$topic])
            || in_array($this->getLastTopic(), array('mvc.response', 'mvc.error'))
        ) {
            // If the topic doesn't exist, or if it's a "final" topic, return 
            // early
            return;
        }

        $args = func_get_args();
        array_shift($args);
        foreach ($this->_topics[$topic] as $handle) {
            $handle->call($args);
            if ('mvc.response' == $this->getLastTopic()) {
                // If the topic published mvc.response, it's time to stop the loop
                return;
            }
        }
        $this->_lastTopic = $topic;
    }

    /**
     * Retrieve last topic published
     * 
     * @return string
     */
    public function getLastTopic()
    {
        return $this->_lastTopic;
    }
}
