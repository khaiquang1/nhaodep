<html>
<head>
    <style>
        body {
            font-family: "Open Sans", Arial, sans-serif;
            font-size: 14px;
            line-height: 22px;
            margin: 0;
            padding: 0;
        }

        table {
            background-color: transparent;
            border-collapse: collapse;
            border-spacing: 0;
            max-width: 100%;
        }

        .main {
            width: 1024px;
            margin: 0 auto;
        }

        .main_detail {
            width: 100%;
            margin: 10px auto;
            float: left;
        }

        .head_item_fl {
            width: 100%;
            float: left;
            margin-bottom: 30px;
            margin-top: -100px !important;
            border-bottom: 1px solid #555;
            padding-bottom: 10px;
        }

        .logo_item {
            width: 50%;
            float: left
        }

        .lt_item {
            width: 50%;
            float: left;
            text-align: right;
            font-size: 18px;
            height: 68px;
            line-height: 68px;
        }

        .detail_view_item {
            float: left;
            height: auto;
            margin-bottom: 20px;
            width: 100%;
        }

        .view_title_bg td {
            background: #7fa637 none repeat scroll 0 0;
            color: #fff;
            font-weight: 700;
        }

        .view_frist {
            border: 0 !important;
            width: 50%;
            float: left;
            padding-left: 0 !important;
            padding-top: 2px !important;
            padding-bottom: 2px !important;
            line-height: 24px;
        }

        .view_second {
            border: 0 !important;
            padding-left: 0 !important;
        }

        .detail_view_item td {
            color: #656565;
            padding: 4px 10px;
        }

        .detail_view_item table tr td {
            border: 1px solid #d6d6d5;
            font-size: 14px;
        }

        .view_bg_one {
            background: #f3f3f3;
        }

        .detail_head_titel {
            /*background: #f3f3f3;*/
            padding: 5px 5px 5px 0;
            width: 100%;
            font-size: 30px;
            height: 44px;
            line-height: 30px;
            box-sizing: border-box;
            margin-bottom: 20px;
            float: left;
        }

        .fl_right {
            float: right
        }
    </style>
</head>
<body>
<div class="main Qdiv-main">
    <div class="main_detail">
        <div class="detail_view_item">
            <div class="view_frist">
                <b>{{trans('quotation.shipping_address')}}:</b><br/>
                {{is_null($quotation->customer)?"":$quotation->customer->address}}
            </div>
            <div class="view_frist">
                {{is_null($quotation->customer)?"":$quotation->customer->address}}
            </div>
        </div>
        <div class="detail_head_titel">{{trans('quotation.quotation_no')}} {{$quotation->quotations_number}}</div>
        <div class="detail_view_item">
            <div class="ViewQ">
                <span><b>{{trans('quotation.customer')}}:</b><br>{{ is_null($quotation->customer)?"":$quotation->customer->full_name }}</span>
            </div>
            <div class="ViewQ">
                <span><b>{{trans('quotation.date')}}:</b><br>{{ $quotation->start_date}}</span>
            </div>
            <div class="ViewQ">
                <span><b>{{trans('quotation.exp_date')}}:</b><br>{{ $quotation->expire_date}}</span>
            </div>
            <div class="ViewQ">
                <span><b>{{trans('quotation.payment_term')}}:</b><br>{{ $quotation->payment_term.' '.trans('quotation.days') }}</span>
            </div>
            <div class="ViewQ">
                <span><b>{{trans('quotation.sales_team_id')}}:</b><br>{{ is_null($quotation->salesTeam)?"":$quotation->salesTeam->salesteam }}</span>
            </div>
            <div class="ViewQ">
                <span><b>{{trans('quotation.sales_person')}}:</b><br>{{ is_null($quotation->salesPerson)?"":$quotation->salesPerson->full_name }}</span>
            </div>
        </div>
        <div class="detail_view_item">
            {{trans('quotation.products')}}
            <table width="100%" cellspacing="0" cellpadding="0" border="" class="table table-bordered">
                <tbody>
                <tr>
                    <td><b>{{trans('quotation.product')}}</b></td>
                    <td><b>{{trans('quotation.quantity')}}</b></td>
                    <td><b>{{trans('quotation.unit_price')}}</b></td>
                    <td><b>{{trans('quotation.taxes')}}</b></td>
                    <td><b>{{trans('quotation.subtotal')}}</b></td>
                </tr>
                @foreach ($quotation->products as $qo_product)
                <tr>
                    <td>{{$qo_product->product_name}}</td>
                    <td>{{ $qo_product->quantity}}</td>
                    <td>{{ $qo_product->price}}</td>
                    <td>{{ number_format($qo_product->quantity * $qo_product->price * floatval(Settings::get('sales_tax')) / 100, 2,
                        '.', '') }}
                    </td>
                    <td>{{ $qo_product->sub_total }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="detail_view_item Q-detailes">
            <table width="100%" cellspacing="0" cellpadding="0" border="" class="pull-right table table-bordered">
                <tbody>
                <tr>
                    <td class="Qtd"><b>{{trans('quotation.untaxed_amount')}}</b></td>
                    <td>{{ $quotation->total }}</td>
                </tr>
                <tr>
                    <td>{{trans('quotation.taxes')}}</td>
                    <td>{{ $quotation->tax_amount }}</td>
                </tr>
                <tr>
                    <td><b>{{trans('quotation.total')}}</b></td>
                    <td>{{ $quotation->grand_total }}</td>
                </tr>
                <tr>
                    <td>{{trans('quotation.discount')}}</td>
                    <td>{{ $quotation->discount }}</td>
                </tr>
                <tr>
                    <td><b>{{trans('quotation.final_price')}}</b></td>
                    <td>{{ $quotation->final_price }}</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>