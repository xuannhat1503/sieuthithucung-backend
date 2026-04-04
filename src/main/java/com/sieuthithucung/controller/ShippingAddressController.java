package com.sieuthithucung.controller;

import com.sieuthithucung.common.AbstractCrudController;
import com.sieuthithucung.dto.ShippingAddressDto;
import com.sieuthithucung.service.ShippingAddressService;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequestMapping("/api/v1/shipping-addresses")
public class ShippingAddressController extends AbstractCrudController<Long, ShippingAddressDto> {

    public ShippingAddressController(ShippingAddressService service) {
        super(service);
    }
}

