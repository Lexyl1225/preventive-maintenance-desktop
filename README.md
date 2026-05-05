# Preventive Maintenance Desktop App

This is a windows application for the preventive maintenance web apps. You can install it on your windows 10 and Windows 11, This apps automatically connects to the cloud database and for the users it is automatically authenticated using firebase realtime database. You can login as Admin or as a user here.
Admin User has the privilege to delete files while the regular user has no privilege to delete any existing files.

How to Create an account?
1. On the login screen clicked on signup button
2. Fill in your Full name, Email Address, Enter you desired Password and Confirm your password
3. You need an existing users (Regular user or an Admin) to proceed with the account creation.
4. Clicked Create Account and your account has been created you will be redirected to login screen
5. Input your Account Email Address and your Password once authenticated you will be redirected to preventive maintenance landing page.
6. You may now start encoding your preventive maintenance reports

## Features
- Preventive maintenance records
    - Branch Code, Branch Name, Location, Date Performed, Name Of Equipment, Task, Status, Perfomed by - The one who performs the Job, Verified by - The on who is present during the maintenance. Next Due - Date of next Preventive maintenance ussually every quarter, Notes - Where you can add more details on the performed Job.
    - View Saved Records - Where you can view your records
    - Edit - Edit your files incase you have made mistakes during encoding
    - Update Button - to update your records
    - Upload Button - To upload additional information about the performed Job, this button accepts images and videos on-site
    - Uploads Gallery - where you can change the image or video link to your PM files
    - Print - This button generates pdf files use the print button inside 'View Saved Records' button to filter your pm reports
    - 'Open Preventive Maintenance Record' - Fetches all available PM records on the database
    - Download JSON - To create backup during server migration
- Load Balancing Records
    - This is where you can Record Electrical Data on PanelBoards, You can Select circuit type depending on the Power System on-site
    - Records measured electrical data on-site and automatically compute total power Consumption in kVA
    - Automatically identify if the circuit is imbalance and there is a suggestion tab where you can re arrange your circuit further balance the load current.
    - Automatically tag the results as balanced if the circuit is balanced three phase
    - Automatically Identify if the system is affected by Harmonics.
- Megger Test Records
    - This records Megger test Result
    - On the the megger test form you can select the type of circuit and the type of IR TEST there are two option only for Fluke PI and DAR, PI - Measures 10mins and above, DAR - Measures 30 seconds period, PI - Polarization Index, DAR - Dielectric absorption ratio
- Preventive Maintenance Checklist
    - This checklist can be recorded and printed 
    - The checklist has 8 sections, you must input all the status of this sections if the section has a a limited items you can remove rows using the -remove button you cal also +add rows if the sections has a lot of item required during inspection.
- Store List
    - Where you can update the store list records for the current month
    - On the store list you can edit or delete store list
    - Add new Area Using the Add Area input field and delete area using the delete button on the card or input field area.
    - Update Preventive Maintenance Last Records.
    - Print updated store list records
- Performance Chart
    - The P Chart - button - Inside this page you can view the group or individual performance of the Electrician who conducted the Preventive Maintenance.
    - Print the performance chart

For more information please visit : https://serverx.ratfish-regulus.ts.net/how-to-use-epm-system/

## Prerequisites
- PHP 7.4+
- Node.js
- Docker (optional)
- MySQL/Database
- Cloudflare account
- Domain Name

## Installation
1. Clone the repo
2. npm install
3. npm start - to test
4. npm run build - build the app
5. see the build output in the root folder /dist/windows.appv1.0.0.exe
6. Install on your Windows Machine
   
## Usage
Install the app in windows 10 or windows 11 Login in the desktop and your done

## Technologies
- PHP, Node.js

## License
(MIT, Apache 2.0)
