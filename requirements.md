## My Personal CRM
1. Login
2. Dashboard: shows summary of how many customer added, how many percentage of each customer status, upcoming followup 10 records, recent 10 action records. Able to select date rage (Default 1 week till today) to export activity records. Able to select date rage (Default 1 week from today) to export followups records. 
3. All Customers Page: This page list all customer with search (using customer name or phone number) filter (by location or ) and pagination the list should show Company Name, Status, Last Contact, Actions (Shows Edit/View buttons).  At the top of the list user can click a "Add Customer" button to add new customer to the list.
4. Customer Form Page (Using for Add, Edit Cusotmer)
    - Customer Detail Includes: Company Name (Required: Text), Address (Not Required: Text), Province (Not Required: Text), Country (Not Required: Text), Company Type (Not Required: Text), Phone (Not Required: Phone), Email (Not Required: Text), Customer Status (Required: Enum: Prospect, Qualified, Not Qualified, Active Customer, Inactive Customer, Closed Won, Closed Lost)
    - Action: Add, user can submit a new record of customer to the database or cancel. After any action return to All customers page.  On create the record, create a default contact person "Company Main Contact" in the Contact Person List
    - Action: Edit, user can edit (update) customer detail, delete customer detail (popup confirmation then delete the record), or cancel. After any action return to All customer page
5. Customer Card Page: This page shows a customer detail in the header as a customer card, this page list all Actions records with this customer, And Contacts Person. User can Add/Edit/Delete Contact Person, And Action History
    - Contact Person Information Table List shows columns: Name, Contact Number, Contact Email, Actions (Edit: on click goto Contact Person Page)
    - Action History Table list of Action history (last update on top) shows Action, Resonse, Next Step, Followup Date Time, Actions(Edit: on click goto Action History Form)
6. Contact Person Form Page: (Use for Add, Edit contact person list)
    - Contact Person information includes: Name (Required: Text), Title (Not Required: Text), Role (Not Required: Text), Contact Number (Not Required: Phone), Contact Email (Not Required: Email)
    - Action Add Contact Person: user can submit new contact person for the company: on click Add Contract Person, create new record and return to its related Customer Card Page. On click Cancel, ignore all changes and return to its related Customer Card Page.
    - Action Edit Contact Person: user can update all contact person information, or click Delete button to delete the contact person. On click Save Contact, save information and return to its related Customer Card Page. On click Cancel, ignore all changes and return to its related Customer Card Page.
7. Action History Form Page (use for add, edit Action History List)
    - Action History Fields includes: Action Time (Required: Datetime - default:now), Action (Required: Text), Responds (Not Required: Text), Next Step (Not Required: Text), Followup Datetime ()
    - Action Add Action Record: user can submit new action record for the company: on click Add Action, create new record and return to its related Customer Card Page. On click Cancel, ignore all changes and return to its related Customer Card Page.
    - Action Edit Action Record: user can update all action record information, or click Delete button to delete the action record. On click Save Record, save information and return to its related Customer Card Page. On click Cancel, ignore all changes and return to its related Customer Card Page.
8. All Activities Page: This page list all activieties for the past 1 month (As default), but user can Filter by company, Date Range and able to sort Ascending or Decending order.
9. All Follow-ups Page: This page list all Followups for the next 1 month (As default), but user can Filter by company, Date Range and able to sort Ascending or Decending order.
10. Setting Page: able to set number per paginations (Default:20) for Customer list in All Customer Page.  Able to Create, Edit, Delete user for the applications.
    * User Information: username (Text), Password(Password), Email(Email), User Role(Admin, User)