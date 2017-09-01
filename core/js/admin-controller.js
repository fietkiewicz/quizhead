angular.module("cwruSelfAssessment",[]).controller("CwruSelfAssessmentAdministration", ['$scope','$http',function($scope,$http) {
    $scope.app = "/index.php";
    $scope.model = {};
    $scope.model.user = null;
    $scope.model.courses = null;
    $scope.model.questionSets=[];
    $scope.model.roster=[];
    $scope.model.selectedCourse=null;
    $scope.model.showAddQuestionSet=false;
    $scope.model.showAddQuestionSetButtonClass="btn btn-default";
    $scope.model.showAddRoster=false;
    $scope.model.showAddRosterButtonClass="btn btn-default";

    $scope.requestObject = {};
    
    $scope.objEmpty = function(obj) {
        for(var field in obj) {
            if(obj.hasOwnProperty(field)) { 
                return false;
            }
        }
        return true;
    };
    
    $scope.isAdmin = function() {
        return $scope.model.admin === true;
    };
    
    $scope.showAddQuestionSetForm = function() {
        if($scope.model.showAddQuestionSet) {
            $scope.model.showAddQuestionSet = false;
            $scope.model.showAddQuestionSetButtonClass="btn btn-default";
        }
        else {
            $scope.model.showAddQuestionSet=true;
            $scope.model.showAddQuestionSetButtonClass="btn btn-success";
        }
    };
    
    $scope.showAddRosterForm = function() {
        if($scope.model.showAddRoster) {
            $scope.model.showAddRoster = false;
            $scope.model.showAddRosterButtonClass="btn btn-default";
        }
        else {
            $scope.model.showAddRoster=true;
            $scope.model.showAddRosterButtonClass="btn btn-success";
        }
    };
    
    $scope.loadMyInfo = function() {
        $http.put($scope.app,{"action":"myinfo"}).
            success(function(data,status,headers,config) {
                $scope.model.user=data["user"];
                $scope.model.admin = data["admin"];
                $scope.model.courses = [];
                for(var key in data["auth"]) {
                    if(data["auth"][key] === 0) {
                        $scope.model.courses.push(key);
                    }
                }
                $scope.model.games = data["games"];
                console.log(data);
            }).
            error(function(data,status,headers,config) {
                console.log(status);
            });
    };
    
    $scope.getQuestionSets = function() {
        $http.put($scope.app,{"action":"qs","course":$scope.model.selectedCourse}).
            success(function(data,status,headers,config) {
                $scope.model.questionSets=[];
                for(var key in data) {
                    $scope.model.questionSets.push({"title":data[key]["title"],"id":key});
                }
                console.log($scope.model.questionSets);
            }).
            error(function(data,status,headers,config) {
                console.log(status);
            });
    };
    
    $scope.getRoster = function() {
        $http.put($scope.app,{"action":"roster","course":$scope.model.selectedCourse}).
            success(function(data,status,headers,config) {
                $scope.model.roster=data;
                console.log($scope.model.roster);
            }).
            error(function(data,status,headers,config) {
                console.log(status);
            });
    };
    
    $scope.deleteQuestionSet = function(questionSet) {
        $http.put($scope.app,{"action":"dqs","id":questionSet.id,"course":$scope.model.selectedCourse}).
            success(function(data,status,headers,config) {
                for(var i=0;i<$scope.model.questionSets.length;i++) {
                    if($scope.model.questionSets[i].id === questionSet.id) {
                       $scope.model.questionSets.splice(i,1); 
                    }
                }
            }).
            error(function(data,status,headers,config) {
                console.log(status);
            });
    };
    
    $scope.deleteUser = function(questionSet) {
        alert("TODO: Not implemented yet");
    };
    
    $scope.onSelectCourse = function() {
        if($scope.model.selectedCourse !== null) {
            $scope.getQuestionSets();
            $scope.getRoster();
        }
    };

    $scope.loadMyInfo();
    
}]);


