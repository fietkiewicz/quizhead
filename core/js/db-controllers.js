angular.module("cwruSelfAssessment",[]).controller("CwruSelfAssessmentDashboard", ['$scope','$http','$interval',function($scope,$http,$interval) {
    $scope.app = "/index.php";
    $scope.model = {};
    $scope.model.user = null;
    $scope.model.courses = null;
    $scope.model.questionSets=[];
    $scope.model.page = 0;
    $scope.model.createGameCourse=null;
    $scope.model.createGameQuestionSet=null;
    $scope.model.createGamePlayers=[];
    $scope.model.instructor=false;
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
    
    $scope.isInstructor = function() {
        return $scope.model.instructor;
    };
    
    $scope.loadMyInfo = function() {
        $http.put($scope.app,{"action":"myinfo"}).
            success(function(data,status,headers,config) {
                $scope.model.user=data["user"];
                $scope.model.admin = data["admin"];
                $scope.model.courses = [];
                for(var key in data["auth"]) {
                    if(data["auth"][key] === 0) {
                        $scope.model.instructor=true;
                    }
                    $scope.model.courses.push(key);
                }
                $scope.model.games = data["games"];
                console.log(data);
            }).
            error(function(data,status,headers,config) {
                console.log(status);
            });
    };
    
    $scope.getQuestionSets = function() {
        $http.put($scope.app,{"action":"qs","course":$scope.model.createGameCourse}).
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
    
    $scope.createGame = function() {
        var request = {"action":"cgame",
                       "course":$scope.model.createGameCourse,
                       "questions":$scope.model.createGameQuestionSet.id,
                       "players":$scope.model.createGamePlayers};
        $http.put($scope.app,request).
            success(function(data,status,headers,config) {
                $scope.loadMyInfo();
                $scope.gtLanding();
            }).
            error(function(data,status,headers,config) {
                alert("An error occured creating the game. Server returned status "+status);
            });
    };
    
    $scope.declineGame = function(game) {
        var request = {"action":"dcgame",
                       "id":game.game};
        $http.put($scope.app,request).
            success(function(data,status,headers,config) {
                for(var i=0;i<$scope.model.games.invited.length;i++) {
                    if($scope.model.games.invited[i].game === game.game) {
                        $scope.model.games.invited.splice(i,1);
                        break;
                    }
                }
            }).
            error(function(data,status,headers,config) {
                alert("An error occured declining the game. Server returned status "+status);
            });
    };
    
    $scope.endGame = function(game) {
        var request = {"action":"endgame",
                       "id":game.game};
        $http.put($scope.app,request).
            success(function(data,status,headers,config) {
                for(var i=0;i<$scope.model.games.created.length;i++) {
                    if($scope.model.games.created[i].game === game.game) {
                        $scope.model.games.created.splice(i,1);
                        break;
                    }
                }
            }).
            error(function(data,status,headers,config) {
                alert("An error occured ending the game. Server returned status "+status);
            });
    };
    
    $scope.addPlayer = function() {
        var players = $scope.model.createGamePlayer.split(",");
        for(var i=0;i<players.length;i++) {
            var player = players[i].trim();
            if(player && $scope.model.createGamePlayers.indexOf(player) === -1) {
                $scope.model.createGamePlayers.push(player);
            }
        }
    };
    
    $scope.deletePlayer = function(player) {
        var pos = $scope.model.createGamePlayers.indexOf(player);
        $scope.model.createGamePlayers.splice(pos,1);
    };
    
    $scope.gtChooseCourse = function() {
        $scope.model.page = 1;
    };
    
    $scope.gtLanding = function() {
        $scope.model.page = 0;
    };
    
    $scope.gtChooseQuestionSet = function() {
        if($scope.model.createGameCourse) {
            $scope.getQuestionSets();
            $scope.model.page = 2;
        }
    };
    
    $scope.gtInvitePlayers = function() {
        if($scope.model.createGameQuestionSet) {
            $scope.model.page = 3;
        }
    };
    
    $scope.gtConfirmGame = function() {
        $scope.model.page = 4;
    };
    
    $scope.landing = function() {
        return $scope.model.page === 0;
    };
    
    $scope.chooseCourse = function() {
        return $scope.model.page === 1;
    };
    
    $scope.chooseQuestionSet = function() {
        return $scope.model.page === 2;
    };
    
    $scope.invitePlayers = function() {
        return $scope.model.page === 3;
    };
    
    $scope.confirmGame = function() {
        return $scope.model.page === 4;
    };
    
    $scope.cancelCreateGame = function() {
        $scope.model.createGameCourse=null;
        $scope.model.createGameQuestionSet=null;
        $scope.model.createGamePlayers=[];
        $scope.gtLanding();
    };
    
    var intid;
    $scope.startUpdates = function() {
        intid = $interval(function() {
            $scope.loadMyInfo();
        },5000);
    };
    
    $scope.stopUpdates = function() {
        $interval.cancel(intid);
        intid = undefined;
    };
    
    $scope.$on("$destroy", function() {
        $scope.stopUpdates();
    });
    
    $scope.loadMyInfo();
    $scope.startUpdates();
    
    
}]);
