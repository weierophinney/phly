<?php

// Allows rendering multiple views; assigns content to $name segment of
// response object.
// $vars is a set of variables to assign to the view.
$response->addView($name, $vars);
$response->getViews();        // returns name => vars pairs
$response->removeView($name); // removes view
$response->clearViews();      // removes all views

// Add metadata. If $name already exists, value is added to an array of data
// for that key.
// Could be used for headers, etc.
$response->addMetadata($name, $value);
$response->getMetadata($name = null);  // Gets all metadata
$response->removeMetadata($name);
$response->clearMetadata();

// Set name of layout to use
$response->setLayout($name);
$response->getLayout();

// Set the renderer to use for the given response
$response->setRenderer($rendererOrClass);
$response->getRenderer();

// Dispatches the renderer, and thus the response
$response->send();
