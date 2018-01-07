<!DOCTYPE html>
<?php
require_once "User.php";
User::checkLogin('n');
?>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Registration | Recipient</title>
</head>
<body>
<!DOCTYPE html>
<html>
<style>
  /* Full-width input fields */
  input[type=text], input[type=password], input[type=tel]  {
    width: 100%;
    padding: 12px 20px;
    margin: 8px 0;
    display: inline-block;
    border: 1px solid #ccc;
    box-sizing: border-box;
  }
  input[type=number] {
    width: 25%;
    padding: 12px 20px;
    margin: 8px 0;
    display: inline-block;
    border: 1px solid #ccc;
    box-sizing: border-box;
  }
  input[type=file]{
    width: 100%;
    padding: 12px 20px;
    margin: 8px 0;
    display: inline-block;
    border: 1px solid #ccc;
    box-sizing: border-box;
  }

  /* Set a style for all buttons */
  button {
    background-color: #4CAF50;
    color: white;
    padding: 14px 20px;
    margin: 8px 0;
    border: none;
    cursor: pointer;
    width: 100%;
  }

  /* Extra styles for the cancel button */
  .cancelbtn {
    padding: 14px 20px;
    background-color: #f44336;
  }

  /* Float cancel and signup buttons and add an equal width */
  .cancelbtn,.signupbtn {
    float: left;
    width: 50%;
  }

  /* Add padding to container elements */
  .container {
    padding: 16px;
  }

  /* Clear floats */
  .clearfix::after {
    content: "";
    clear: both;
    display: table;
  }

  /* Change styles for cancel button and signup button on extra small screens */
  @media screen and (max-width: 300px) {
    .cancelbtn, .signupbtn {
      width: 100%;
    }
  }
</style>
<body>
<h2>Register as Recipient</h2>

<form action="registerRecipientHandler.php" method="post"  enctype="multipart/form-data" style="border:1px solid #ccc" >
  <div class="container">
    <label><b>Name</b></label>
    <input type="text" placeholder="Enter Name" name="name" required autofocus>

    <label><b>Username</b></label>
    <input type="text" placeholder="Enter Username" name="user_id" maxlength="10" required>

    <label><b>Age</b></label><P></P>
    <input type="number" placeholder="Enter Age" name="age" required><P></P>

    <label><b>Occupation</b></label>
    <input type="text" placeholder="Enter Occupation" name="occupation" required>

    <label><b>Place of Study/Work</b></label>
    <input type="text" placeholder="Enter Place of study/work" name="place_of_work" required>

    <label><b>Salary</b></label>
    <input type="text" placeholder="Enter salary" name="salary" required>

    <label><b>NIC Number</b></label>
    <input type="text" placeholder="Enter NIC" name="nic" maxlength="10" required>

    <label><b>Email</b></label>
    <input type="text" placeholder="Enter Email" name="email" required>

    <label><b>Contact number</b></label>
    <input type="tel" placeholder="Enter contact number" name="phone" maxlength="10" required>

    <label><b>Address</b></label>
    <input type="text" placeholder="Enter address" name="address" required>

    <label><b>Proof Document (one PDF file , max file size 2mb)</b></label>
    <input type="file" placeholder="upload" name="fileToUpload" id="fileToUpload">

    <label><b>Summary to be shown to the donor</b></label>
    <input type="text" placeholder="Summary" name="summary" required>

    <label><b>Password</b></label>
    <input type="password" placeholder="Enter Password" name="pwd" required>

    <label><b>Repeat Password</b></label>
    <input type="password" placeholder="Repeat Password" name="repeat_pwd" required>
    <input type="checkbox" checked="checked"> Remember me

    <p>By creating an account you agree to our <a href="#">Terms & Privacy</a>.</p>

    <div class="clearfix">
      <button type="reset" class="cancelbtn">Reset Form</button>
      <button type="submit" class="signupbtn">Sign Up</button>
    </div>
  </div>
</form>

</body>
</html>

</body>
</html>