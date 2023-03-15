<style>
    .v-select {
        margin-bottom: 5px;
    }

    .v-select.open .dropdown-toggle {
        border-bottom: 1px solid #ccc;
    }

    .v-select .dropdown-toggle {
        padding: 0px;
        height: 25px;
    }

    .v-select input[type=search],
    .v-select input[type=search]:focus {
        margin: 0px;
    }

    .v-select .vs__selected-options {
        overflow: hidden;
        flex-wrap: nowrap;
    }

    .v-select .selected-tag {
        margin: 2px 0px;
        white-space: nowrap;
        position: absolute;
        left: 0px;
    }

    .v-select .vs__actions {
        margin-top: -5px;
    }

    .v-select .dropdown-menu {
        width: auto;
        overflow-y: auto;
    }
</style>
<div id="customerListReport">
    <div class="row" style="margin-bottom: 8px;">
        <div class="col-md-2">
            <select class="form-control" style="padding: 0px 6px;" @change="CustomerListChange">
                <option value="">Select Item</option>
                <option value="area">By Area</option>
                <option value="mobile">By Mobile Number</option>
            </select>
        </div>
        <div class="col-md-2" v-bind:style="{display: mobile ? '':'none'}">
            <v-select v-bind:options="customers" v-model="selectedCustomer" label="Customer_Mobile" @input="mobileChange"></v-select>
        </div>
        <div class="col-md-2" v-bind:style="{display: area ? '':'none'}">
            <v-select v-bind:options="districts" v-model="selectedDistrict" label="District_Name" @input="areaChange"></v-select>
        </div>
    </div>
    <div style="display:none;" v-bind:style="{display: customers.length > 0 ? '' : 'none'}">
        <div class="row">
            <div class="col-md-12">
                <a href="" @click.prevent="printCustomerList"><i class="fa fa-print"></i> Print</a>
            </div>
        </div>

        <div class="row" style="margin-top:15px;">
            <div class="col-md-12">
                <div class="table-responsive" id="printContent">
                    <table class="table table-bordered table-condensed">
                        <thead>
                            <th>Sl</th>
                            <th>Customer Id</th>
                            <th>Customer Name</th>
                            <th>Address</th>
                            <th>Contact No.</th>
                        </thead>
                        <tbody>
                            <tr v-for="(customer, sl) in customers">
                                <td>{{ sl + 1 }}</td>
                                <td>{{ customer.Customer_Code }}</td>
                                <td>{{ customer.Customer_Name }}</td>
                                <td>{{ customer.Customer_Address }} {{ customer.District_Name }}</td>
                                <td>{{ customer.Customer_Mobile }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div style="display:none;text-align:center;" v-bind:style="{display: customers.length > 0 ? 'none' : ''}">
        No records found
    </div>
</div>

<script src="<?php echo base_url(); ?>assets/js/vue/vue.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/vue/axios.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/vue/vue-select.min.js"></script>

<script>
    Vue.component('v-select', VueSelect.VueSelect);
    new Vue({
        el: '#customerListReport',
        data() {
            return {
                customers: [],
                districts: [],
                mobile: false,
                area: false,
                selectedCustomer: {
                    Customer_Mobile: "Select Mobile Number"
                },
                selectedDistrict: {
                    District_Name: "Select Area"
                }
            }
        },
        created() {
            this.getCustomers();
        },
        methods: {
            getCustomers() {
                axios.get('/get_customers').then(res => {
                    this.customers = res.data;
                })
            },
            areawiseCustomer() {
                axios.get('/get_customers').then(res => {
                    this.customers = res.data.filter(p => p.area_ID == this.selectedDistrict.District_SlNo);
                })
            },
            getDistricts() {
                axios.get('/get_districts').then(res => {
                    this.districts = res.data;
                })
            },

            CustomerListChange(event) {
                if (event.target.value == "mobile") {
                    this.mobile = true
                    this.area = false
                    this.selectedDistrict = {
                        District_Name: "Select Area"
                    }

                } else if (event.target.value == "area") {
                    this.area = true
                    this.mobile = false
                    this.getDistricts()
                    this.selectedCustomer = {
                        Customer_Mobile: "Select Mobile Number"
                    }
                } else {
                    this.area = false
                    this.mobile = false
                    this.getCustomers()
                    this.selectedCustomer = {
                        Customer_Mobile: "Select Mobile Number"
                    }
                    this.selectedDistrict = {
                        District_Name: "Select Area"
                    }
                }
            },

            areaChange() {
                if (this.selectedDistrict == null) {
                    this.getDistricts()
                    return
                }
                if (this.selectedDistrict.District_SlNo == undefined) {
                    this.getCustomers()
                } else {
                    this.areawiseCustomer();
                }
            },
            mobileChange() {
                if (this.selectedCustomer == null) {
                    this.getCustomers()
                    return
                }
                if (this.selectedCustomer.Customer_SlNo == undefined) {
                    this.getCustomers()
                } else {
                    this.customers = this.customers.filter(res => res.Customer_Mobile == this.selectedCustomer.Customer_Mobile);
                }
            },

            async printCustomerList() {
                let printContent = `
                    <div class="container">
                        <h4 style="text-align:center">Customer List</h4 style="text-align:center">
						<div class="row">
							<div class="col-xs-12">
								${document.querySelector('#printContent').innerHTML}
							</div>
						</div>
                    </div>
                `;

                let printWindow = window.open('', '', `width=${screen.width}, height=${screen.height}`);
                printWindow.document.write(`
                    <?php $this->load->view('Administrator/reports/reportHeader.php'); ?>
                `);

                printWindow.document.body.innerHTML += printContent;
                printWindow.focus();
                await new Promise(r => setTimeout(r, 1000));
                printWindow.print();
                printWindow.close();
            }
        }
    })
</script>