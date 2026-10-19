<?php
require __DIR__ . '/../app/Libraries/RepositorySoftwareDetector.php';
use App\Libraries\RepositorySoftwareDetector as Detector;
$expect = static function ($expected, $actual, string $label): void {
    if ($expected !== $actual) throw new RuntimeException($label . ': ' . json_encode($actual));
};
$expect(['name' => 'DSpace', 'version' => '8.1'], Detector::api('DSpace', '{"dspaceVersion":"DSpace 8.1"}'), 'DSpace REST');
$expect(['name' => 'Dataverse', 'version' => '6.4'], Detector::api('Dataverse', '{"status":"OK","data":{"version":"6.4","build":"1234"}}'), 'Dataverse API');
$expect(['name' => 'CKAN', 'version' => '2.11.1'], Detector::api('CKAN', '{"success":true,"result":{"ckan_version":"2.11.1"}}'), 'CKAN API');
foreach (['DSpace 7.6' => ['DSpace', '7.6'], 'Dataverse v6.4' => ['Dataverse', '6.4'], 'CKAN 2.10.4' => ['CKAN', '2.10.4'], 'InvenioRDM 12.0' => ['Invenio', '12.0'], 'Invenio' => ['Invenio', null]] as $text => [$name, $version]) {
    $expect(['name' => $name, 'version' => $version], Detector::html('<meta content="' . $text . '" name="generator">'), 'Generator ' . $text);
}
$expect(['name' => 'Invenio', 'version' => '12.0'], Detector::html('<footer><a href="https://inveniosoftware.org">Powered by InvenioRDM 12.0</a></footer>'), 'Product footer');
$expect([], Detector::html('<p>Research comparing DSpace 7.6 and Dataverse 6.4</p>'), 'Ignore research content');
$expect([], Detector::html(''), 'Empty page');
$expect([], Detector::api('DSpace', '<html>Error 404</html>'), 'Invalid JSON');
$expect([], Detector::api('CKAN', '{"success":false,"result":{"ckan_version":"2.9"}}'), 'Failed API');
$expect([], Detector::api('Dataverse', '{"version":"1.0"}'), 'Unrelated API');
$expect(null, Detector::version('unknown'), 'Unknown version');
echo "PASS: repository software APIs, generator metadata, attribution, versions and false positives.\n";
