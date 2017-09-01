angular.module("cwruSelfAssessment",[]).controller("CwruSelfAssessmentGame", ['$scope','$http','$location','$interval',function($scope,$http,$location,$interval) {
    $scope.app = "/index.php";
    $scope.model = {};
    $scope.model.user = null;
    $scope.model.questions = null;
    $scope.model.progress = null;
    $scope.model.qn = 0;
        
    $scope.loadMyInfo = function() {
        $http.put($scope.app,{"action":"myinfo"}).
            success(function(data,status,headers,config) {
                $scope.model.user=data["user"];
            }).
            error(function(data,status,headers,config) {
                console.log(status);
            });
    };
    
    $scope.getQuestions = function(id) {
        $http.put($scope.app,{"action":"play","game":id}).
            success(function(data,status,headers,config) {
                $scope.model.questions = data;
                console.log(data);
            }).
            error(function(data,status,headers,config) {
                console.log(status);
            });
    };
    
    $scope.getProgress = function(id) {
        $http.put($scope.app,{"action":"progress","game":id}).
            success(function(data,status,headers,config) {
                $scope.model.progress = data;
                console.log(data);
            }).
            error(function(data,status,headers,config) {
                console.log(status);
            });
    };
    
    $scope.answerQuestion = function() {
        if($scope.model.answer === undefined) {
            return;
        }
        
        $http.put($scope.app,{"action":"answer","game":id,"question":$scope.model.questions[$scope.model.qn].id,"choice":$scope.model.answer}).
            success(function(data,status,headers,config) {
                console.log(data);
                $scope.model.qn++;
            }).
            error(function(data,status,headers,config) {
                console.log(status);
            });
    };
    
    $scope.score = function(player) {
        if(player.qn === 1) {
            return "";
        }
        else {
            return Math.floor(100*player.score/(player.qn-1))+"%";
        }
    };
    
    var intid;
    var id;
    $scope.startProgressUpdates = function() {
        intid = $interval(function() {
            $scope.getProgress(id);
        },5000);
    };
    
    $scope.stopProgressUpdates = function() {
        $interval.cancel(intid);
        intid = undefined;
    };
    
    $scope.$on("$destroy", function() {
        $scope.stopProgressUpdates();
    });
    
    function getId() {
        var url = $location.absUrl();
        var s = url.indexOf("game=");
        if(s !== -1) {
            s += 5;
            var e = url.indexOf("&",s);
            if(e !== -1) {
                return url.substring(s,e);
            }
            else {
                return url.substring(s);
            }
        }
        return null;
    }
    $scope.loadMyInfo();
    
    var id = getId();
    if(id !== null) {
        $scope.getQuestions(id);
        $scope.getProgress(id);
        $scope.startProgressUpdates();
    }
}]);
