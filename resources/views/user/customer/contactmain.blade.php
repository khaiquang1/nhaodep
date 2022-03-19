@extends('layouts.user')

{{-- Web site Title --}}
@section('title')
    {{ $title }}
@stop

{{-- Content --}}
@section('content')
    <div class="box1">
        <div class="row" id="contact">
        <div class="col-md-4 col-sm-4">
            <div class="input-group" id="search">
                <span class="input-group-addon no-border no-bg"><i class="fa fa-search"></i></span>
                <input type="text" class="form-control no-border no-bg" placeholder="Search Contacts">
            </div>
            <div class="tabbable custom-tab">
                <ul >
                    <li class="active"><a href="#tab1" data-toggle="tab"><img src="{{asset('img/avatar.jpg')}}">
                            <span>Alexander</span><br><span><i class="fa fa-phone"></i> 3543654738</span></a> </li>
                    <li><a href="#tab2" data-toggle="tab"><img src="{{asset('img/avatar1.jpg')}}">
                            <span>Gregory P. Bryant</span><br><span><i class="fa fa-phone"></i> 5465421515</span></a> </li>
                    <li><a href="#tab3" data-toggle="tab"><img src="{{asset('img/avatar7.jpg')}}">
                            <span>Dolores J. Nieto</span><br><span><i class="fa fa-phone"></i> 2315478903</span></a> </li>
                    <li><a href="#tab4" data-toggle="tab"><img src="{{asset('img/avatar5.jpg')}}">
                            <span>Heidi G. Monn</span><br><span><i class="fa fa-phone"></i> 5487963120</span></a> </li>
                    <li><a href="#tab5" data-toggle="tab"><img src="{{asset('img/avatar6.jpg')}}">
                            <span>Rocco E. Tiernan</span><br><span><i class="fa fa-phone"></i> 3451378027</span></a> </li>
                    <li><a href="#tab6" data-toggle="tab"><img src="{{asset('img/avatar7.jpg')}}">
                            <span>Serina K. Wallner</span><br><span><i class="fa fa-phone"></i> 2457896310</span></a> </li>
                    <li><a href="#tab7" data-toggle="tab"><img src="{{asset('img/avatar.jpg')}}">
                            <span>Tracie D. Dickinson</span><br><span><i class="fa fa-phone"></i> 7589463012</span></a> </li>
                    <li><a href="#tab8" data-toggle="tab"><img src="{{asset('img/avatar1.jpg')}}">
                            <span>Rebeca Dias Cardoso</span><br><span><i class="fa fa-phone"></i> 5446781293</span></a> </li>
                    <li><a href="#tab9" data-toggle="tab"><img src="{{asset('img/avatar7.jpg')}}">
                            <span>Tain Tseng</span><br><span><i class="fa fa-phone"></i> 6445612789</span></a> </li>
                    <li><a href="#tab10" data-toggle="tab"><img src="{{asset('img/avatar5.jpg')}}">
                            <span>Feng Fan</span><br><span><i class="fa fa-phone"></i> 8794561328</span></a> </li>
                    <li><a href="#tab11" data-toggle="tab"><img src="{{asset('img/avatar6.jpg')}}">
                            <span>De Chiu</span><br><span><i class="fa fa-phone"></i> 4696357473</span></a> </li>
                    <li><a href="#tab12" data-toggle="tab"><img src="{{asset('img/avatar7.jpg')}}">
                            <span>Ni Chuang</span><br><span><i class="fa fa-phone"></i> 6565468415</span></a> </li>
                </ul>
            </div>
        </div>
        <div class="col-md-8 col-sm-8">
            <a href="#" class="btn btn-default text-center mar-right4"><i class="fa fa-download"></i> Import Contact</a>
            <a href="{{ route($type.'.create') }}" class="btn btn-default"><i class="fa fa-plus"></i> New Contact</a>
            <br>
            <br>
            <div class="tab-content">
                <div class="tab-pane active" id="tab1">
                    <div class="row">
                        <div class="col-md-3 col-sm-3 col-xs-4">
                            <img src="{{asset('img/avatar.jpg')}}" alt="user-avatar">
                            <ul class="cnt-details">
                                <li>Email: </li>
                                <li>Mobile: </li>
                                <li>Fax: </li>
                                <li>Company: </li>
                                <li>Website: </li>
                            </ul>
                        </div>
                        <div class="col-md-9 col-sm-9 col-xs-8">
                            <h3 class="mar-left5">Alexander</h3>
                            <ul class="cnt-names">
                                <li> Alexander@lcrm.com</li>
                                <li> +3543654738</li>
                                <li> +2549876354</li>
                                <li> LCRM</li>
                                <li> http://testlcrm.com</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="tab-pane" id="tab2">
                    <div class="row">
                        <div class="col-md-3">
                            <img src="{{asset('img/avatar1.jpg')}}" alt="user-avatar">
                            <ul class="cnt-details">
                                <li>Email: </li>
                                <li>Mobile: </li>
                                <li>Fax: </li>
                                <li>Company: </li>
                                <li>Website: </li>
                            </ul>
                        </div>
                        <div class="col-md-9">
                            <h3 class="mar-left5">Gregory P. Bryant</h3>
                            <ul class="cnt-names">
                                <li> gregorybryant@dfas.com</li>
                                <li> +5465421515</li>
                                <li> +2549876354</li>
                                <li> Pro Star</li>
                                <li> http://seekFashions.is</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="tab-pane" id="tab3">
                    <div class="row">
                        <div class="col-md-3">
                            <img src="{{asset('img/avatar7.jpg')}}" alt="user-avatar">
                            <ul class="cnt-details">
                                <li>Email: </li>
                                <li>Mobile: </li>
                                <li>Fax: </li>
                                <li>Company: </li>
                                <li>Website: </li>
                            </ul>
                        </div>
                        <div class="col-md-9">
                            <h3 class="mar-left5">Gregory P. Bryant</h3>
                            <ul class="cnt-names">
                                <li> gregoryPBryant@teleworm.us</li>
                                <li> +2315478903</li>
                                <li> +2549876354</li>
                                <li> Custom Lawn Care</li>
                                <li> http://amphidea.com</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="tab-pane" id="tab4">
                    <div class="row">
                        <div class="col-md-3">
                            <img src="{{asset('img/avatar5.jpg')}}" alt="user-avatar">
                            <ul class="cnt-details">
                                <li>Email: </li>
                                <li>Mobile: </li>
                                <li>Fax: </li>
                                <li>Company: </li>
                                <li>Website: </li>
                            </ul>
                        </div>
                        <div class="col-md-9">
                            <h3 class="mar-left5">Dolores J. Nieto</h3>
                            <ul class="cnt-names">
                                <li> doloresJNieto@armyspy.com</li>
                                <li> +5487963120</li>
                                <li> +2549876354</li>
                                <li> Strategic Profit</li>
                                <li> http://anisamidea.com</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="tab-pane" id="tab5">
                    <div class="row">
                        <div class="col-md-3">
                            <img src="{{asset('img/avatar6.jpg')}}" alt="user-avatar">
                            <ul class="cnt-details">
                                <li>Email: </li>
                                <li>Mobile: </li>
                                <li>Fax: </li>
                                <li>Company: </li>
                                <li>Website: </li>
                            </ul>
                        </div>
                        <div class="col-md-9">
                            <h3 class="mar-left5">Heidi G. Monn</h3>
                            <ul class="cnt-names">
                                <li> heidiGMonn@teleworm.us</li>
                                <li> +3451378027</li>
                                <li> +2549876354</li>
                                <li> Druther's</li>
                                <li> http://aphicidea.com</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="tab-pane" id="tab6">
                    <div class="row">
                        <div class="col-md-3">
                            <img src="{{asset('img/avatar7.jpg')}}" alt="user-avatar">
                            <ul class="cnt-details">
                                <li>Email: </li>
                                <li>Mobile: </li>
                                <li>Fax: </li>
                                <li>Company: </li>
                                <li>Website: </li>
                            </ul>
                        </div>
                        <div class="col-md-9">
                            <h3 class="mar-left5">Rocco E. Tiernan</h3>
                            <ul class="cnt-names">
                                <li> roccoETiernan@jourrapide.com</li>
                                <li> +2457896310</li>
                                <li> +2549876354</li>
                                <li> Janeville</li>
                                <li> http://alarmedical.com</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <a href="#" class="btn btn-primary edit"> <i class="fa fa-pencil fa-fw"></i> Edit</a>
    </div>
    </div>

@stop

{{-- Scripts --}}
@section('scripts')

@stop
