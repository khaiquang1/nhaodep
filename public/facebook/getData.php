<?php
/**
 * Copyright (c) 2015-present, Facebook, Inc. All rights reserved.
 *
 * You are hereby granted a non-exclusive, worldwide, royalty-free license to
 * use, copy, modify, and distribute this software in source code or binary
 * form for use in connection with the web services and APIs provided by
 * Facebook.
 *
 * As with any software that integrates with the Facebook platform, your use
 * of this software is subject to the Facebook Developer Principles and
 * Policies [http://developers.facebook.com/policy/]. This copyright notice
 * shall be included in all copies or substantial portions of the software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 *
 */

require __DIR__ . '/vendor/autoload.php';

use FacebookAds\Object\AdAccount;
use FacebookAds\Object\AdsInsights;
use FacebookAds\Api;
use FacebookAds\Logger\CurlLogger;

$access_token = 'EAAK481QSfNIBAFxk3ZB7KyWUHDcyh4skaSIBmK53h7ANg2qq8vLfLDrQMNpgGmdSHdt9VRKvhJ4OGUxjXG4SlmxpZB3fIRz0eRwoJZCoZC9NNcDBZCSGEYd6ZAdNWxIRGx7rZCEqw3A132Pa63Xyz8gPnbiCl9vOgkL7xBduJIGcmmzmeN4ag3LUDL2xUHhDtUZD';
$ad_account_id = 'act_388866365396011';
$app_secret = 'b5091bcba3128b45d944f651362c2b91';
$app_id = '766305180482770';

$api = Api::init($app_id, $app_secret, $access_token);
$api->setLogger(new CurlLogger());

$fields = array(
  'actions:lead',
  'action_values:lead'
);
$params = array(
  'level' => 'campaign',
  'filtering' => array(array('field' => 'objective','operator' => 'IN','value' => array('LEAD_GENERATION'))),
  'breakdowns' => array(),
  'time_range' => array('since' => '2019-09-17','until' => '2019-10-17'),
);
echo json_encode((new AdAccount($ad_account_id))->getInsights(
  $fields,
  $params
)->getResponse()->getContent(), JSON_PRETTY_PRINT);

