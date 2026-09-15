# CCTN Data Flow Diagrams: Levels 0-2

These diagrams describe the implemented BCTVI/CCTN Online Cable Service Management System. The editable Draw.io version is in `docs/diagrams/CCTN-DFD.drawio`.

## DFD notation

- Rectangle: external entity
- Rounded process: system process or subprocess
- Database cylinder: logical data store
- Arrow: named data flow

## Level 0: Context diagram

Level 0 treats the complete CCTN platform as one process. It shows only data crossing the system boundary and intentionally omits internal data stores.

```mermaid
flowchart LR
    Visitor[Website Visitor] -->|service inquiry, registration details, chat message| System((0. CCTN Online Cable Service Management System))
    System -->|plans, registration result, assistant reply| Visitor

    Client[Client / Subscriber] -->|credentials, profile updates, booking and payment data, maintenance request| System
    System -->|session, account data, booking status, billing, alerts| Client

    WalkIn[Walk-in Applicant] -->|application details and payment through staff| System
    System -->|booking confirmation and receipt| WalkIn

    Admin[Administrator / Staff] -->|credentials, approvals, schedules, billing, resource updates, report filters| System
    System -->|dashboard, queues, records, alerts, receipts and reports| Admin

    Google[Google Identity Platform] -->|verified identity profile| System
    System -->|OAuth authorization request| Google
```

## Level 1: Major system processes

Level 1 decomposes Process 0 into the main business functions found in the Laravel web portal and Android API.

```mermaid
flowchart TB
    Client[Client / Subscriber]
    Admin[Administrator / Staff]
    Visitor[Website Visitor]
    WalkIn[Walk-in Applicant]
    Google[Google Identity Platform]

    P1([1.0 Account and Session Management])
    P2([2.0 Appointment Booking and Scheduling])
    P3([3.0 Appointment Review and Installation Processing])
    P4([4.0 Billing and Payment Management])
    P5([5.0 Maintenance Request Handling])
    P6([6.0 Service and Resource Management])
    P7([7.0 Notification Management])
    P8([8.0 Chatbot Assistant])
    P9([9.0 Reporting and Analytics])

    D1[(D1 Clients)]
    D2[(D2 Admins)]
    D3[(D3 Services)]
    D4[(D4 Time Slots)]
    D5[(D5 Appointments)]
    D6[(D6 Billing Accounts)]
    D7[(D7 Payments)]
    D8[(D8 Maintenance Requests)]
    D9[(D9 Notifications)]
    D10[(D10 Equipment)]
    D11[(D11 Manpower)]
    D12[(D12 Access Tokens)]
    D13[(D13 Uploaded Documents)]
    D14[(D14 Client Payment Methods)]

    Client <--> P1
    Admin <--> P1
    Google <--> P1
    P1 <--> D1
    P1 <--> D2
    P1 --> D12

    Client <--> P2
    D3 --> P2
    D4 --> P2
    D14 --> P2
    P2 --> D5
    P2 --> D13

    Admin <--> P3
    WalkIn <--> P3
    P3 <--> D5
    P3 --> D1

    Client --> P4
    Admin <--> P4
    P4 <--> D6
    P4 --> D7
    D1 --> P4

    Client --> P5
    Admin <--> P5
    P5 <--> D8

    Admin <--> P6
    P6 <--> D3
    P6 <--> D4
    P6 <--> D10
    P6 <--> D11

    Client <--> P7
    Admin <--> P7
    P7 <--> D9

    Visitor <--> P8
    Client <--> P8
    D3 --> P8
    D5 --> P8
    D6 --> P8

    Admin <--> P9
    D5 --> P9
    D6 --> P9
    D7 --> P9

    P2 -. booking event .-> P7
    P3 -. status event .-> P7
    P4 -. payment event .-> P7
    P5 -. maintenance event .-> P7
```

## Level 2: Process 2.0 appointment booking and scheduling

This decomposition follows the implemented web and mobile booking flow. When a requested slot is occupied, the system searches active time slots for the next opening within 14 days. Online bookings require a non-cash payment method, transaction reference, and receipt image.

```mermaid
flowchart TB
    Client[Client / Subscriber]
    Admin[Administrator / Staff]

    P21([2.1 Capture Booking Request])
    P22([2.2 Validate Service and Payment Data])
    P23([2.3 Check Slot Availability])
    P24([2.4 Allocate or Auto-Reschedule])
    P25([2.5 Store Payment Proof])
    P26([2.6 Create Pending Appointment])
    P27([2.7 Issue Confirmation and Alert])

    D1[(D1 Clients)]
    D3[(D3 Services)]
    D4[(D4 Time Slots)]
    D5[(D5 Appointments)]
    D9[(D9 Notifications)]
    D13[(D13 Uploaded Documents)]
    D14[(D14 Client Payment Methods)]

    Client -->|service, date, time, message, payment details and proof| P21
    D1 -->|authenticated client profile| P21
    P21 -->|captured request| P22
    D3 -->|active service and price| P22
    D14 -->|saved or default payment method| P22
    P22 -->|validated booking request| P23
    D4 -->|active slots| P23
    D5 -->|booked date and time pairs| P23
    P23 -->|availability result| P24
    P24 -->|allocated date and time| P25
    P24 -->|no slot found within 14 days| P27
    P25 -->|validated receipt image| D13
    P25 -->|proof path and allocated slot| P26
    P26 -->|pending appointment| D5
    P26 -->|booking reference and reschedule flag| P27
    P27 -->|confirmation, reschedule, or no-slot result| Client
    P27 -->|administrator alert record| D9
    P27 -->|new booking alert| Admin
```

## Level balancing

| Parent process | Input/output preserved in its child diagram |
|---|---|
| Process 0 to Level 1 | Visitor, client, walk-in applicant, administrator, and Google identity flows remain represented. |
| Process 2.0 to Level 2 | Client booking/payment input, availability and booking result output, uploaded proof, appointment record, and administrator alert remain represented. |

## Data-store index

| ID | Implementation |
|---|---|
| D1 | `clients` |
| D2 | `admins` |
| D3 | `services` |
| D4 | `time_slots` |
| D5 | `appointments` |
| D6 | `billing_accounts` |
| D7 | `payments` |
| D8 | `maintenance_requests` |
| D9 | `notifications` |
| D10 | `equipment` |
| D11 | `manpower` |
| D12 | `personal_access_tokens` |
| D13 | uploaded files in public storage |
| D14 | `client_payment_methods` |
