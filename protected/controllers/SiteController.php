<?php

/**
 * SiteController
 * 
 * SiteController class inherit controller (base) class .
 * Actions are defined in siteController.
 * 
 * Copyright (c) 2013 <ahref Foundation -- All rights reserved.
 * Author: Rahul Tripathi<rahul@incaendo.com>
 * This file is part of <Aggregator>.
 * This file can not be copied and/or distributed without the express permission of <ahref Foundation.
 */

class SiteController extends Controller {

 /**
  * actionIndex
  * 
  * This is the default 'index' action that is invoked
  * when an action is not explicitly requested by users.
  */
  public function actionIndex() {
    $this->render('index');
  }
}