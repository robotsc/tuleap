angular
    .module('campaign')
    .controller('CampaignNewCtrl', CampaignNewCtrl);

CampaignNewCtrl.$inject = [
    '$scope',
    '$modalInstance',
    '$state',
    '$filter',
    'gettextCatalog',
    'CampaignService',
    'DefinitionService',
    'SharedPropertiesService'
];

function CampaignNewCtrl(
    $scope,
    $modalInstance,
    $state,
    $filter,
    gettextCatalog,
    CampaignService,
    DefinitionService,
    SharedPropertiesService
) {
    var project_id              = SharedPropertiesService.getProjectId(),
        controller_is_destroyed = false;

    _.extend($scope, {
        ITEMS_PER_PAGE:         15,
        nb_total_definitions:   0,
        loading_definitions:    true,
        definitions:            [],
        submitting_campaign:    false,
        select_all:             false,
        getFilteredDefinitions: getFilteredDefinitions,
        createCampaign:         createCampaign,
        cancel:                 cancel,
        campaign: {
            label:        ''
        }
    });

    getDefinitions(project_id, 750, 0);

    $scope.$on('$destroy', function iVeBeenDismissed() {
        controller_is_destroyed = true;
    });

    function createCampaign(campaign) {
        $scope.submitting_campaign = true;

        CampaignService
            .createCampaign({
              project_id:   project_id,
              label:        campaign.label,
              milestone_id: SharedPropertiesService.getCurrentMilestone().id,
            })
            .then(function () {
                $modalInstance.close();
                $state.go('campaigns.list', {}, {reload: true});
            })
            .finally(function () {
                $scope.submitting_campaign = false;
            });
    }

    function cancel() {
        $modalInstance.dismiss();
    }

    function getDefinitions(project_id, limit, offset) {
        DefinitionService.getDefinitions(project_id, limit, offset).then(function(data) {
            $scope.definitions = $scope.definitions.concat(data.results);
            $scope.nb_total_definitions = data.total;

            if (! controller_is_destroyed && $scope.definitions.length < $scope.nb_total_definitions) {
                getDefinitions(project_id, limit, offset + limit);
            } else {
                $scope.loading_definitions = false;
            }
        });
    }

    function getFilteredDefinitions(filter) {
        return $filter('InPropertiesFilter')(
            $scope.definitions,
            filter,
            ['id','summary','category']
        );
    }
}
