<!doctype html>
<html data-ng-app="cwruSelfAssessment">
 <head>
  <title>Self Assessment Game</title>
  <meta charset="utf-8">
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="css/game.css">
  <script type="text/javascript" src="js/angular.min.js"></script>
  <script type="text/javascript" src="js/admin-controller.js"></script>
 </head>
 <body data-ng-controller="CwruSelfAssessmentAdministration">
     <h1>Self Assessment Game</h1>
     <div id="msg">
         Logged in as <span data-ng-bind="model.user"></span>.
     </div>
     <div id="admin">
         <a href="index.php">Dashboard</a>
     </div>
     <div>
        <select data-ng-model="model.selectedCourse" data-ng-change="onSelectCourse()" data-ng-options="course for course in model.courses">
            <option value="">---Select A Course To Edit---</option>
        </select><br/>
        <div id="db-left-col">
            <div data-ng-if="model.selectedCourse !== null">
                <h2>Roster <button data-ng-click="showAddRosterForm()" data-ng-class="model.showAddRosterButtonClass">+</button></h2>
                <div style="text-align:left">
                    <form data-ng-if="model.showAddRoster" action="index.php?route=admin" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="type" value="roster" />
                        <input type="hidden" name="course" data-ng-value="model.selectedCourse" />
                        <input type="file" name="data" />
                        <input type="submit" value="Upload" />
                    </form>
                    <hr />
                    <table data-ng-if="!objEmpty(model.roster)">
                        <tr>
                            <th>Case ID&nbsp;&nbsp;</th>
                            <th>Actions</th>
                        </tr>
                        <tr data-ng-repeat="student in model.roster">
                            <td data-ng-bind="student"></td>
                            <td><button class="btn btn-danger" data-ng-click="deleteUser(student)">Delete</button></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div id="db-right-col">
            <div data-ng-if="model.selectedCourse !== null">
                <h2>Question Sets <button data-ng-click="showAddQuestionSetForm()" data-ng-class="model.showAddQuestionSetButtonClass">+</button></h2>
                <div style="text-align:left">
                    <form data-ng-if="model.showAddQuestionSet" action="index.php?route=admin" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="type" value="questions" />
                        <input type="hidden" name="course" data-ng-value="model.selectedCourse" />
                        Title : <input type="text" name="title" value="" /><br/>
                        <input type="file" name="data" />
                        <input type="submit" value="Upload" />
                    </form>
                    <hr />
                    <table data-ng-if="!objEmpty(model.questionSets)">
                        <tr>
                            <th>Title&nbsp;&nbsp;</th>
                            <th>Actions</th>
                        </tr>
                        <tr data-ng-repeat="questionSet in model.questionSets">
                            <td data-ng-bind="questionSet.title"></td>
                            <td><button class="btn btn-danger" data-ng-click="deleteQuestionSet(questionSet)">Delete</button></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
     </div>
     <div id="footer">Copyright &copy; 2015 Case Western Reserve University</div>
 </body>
</html>
