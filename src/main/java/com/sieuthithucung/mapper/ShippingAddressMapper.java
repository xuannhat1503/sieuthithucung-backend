package com.sieuthithucung.mapper;

import com.sieuthithucung.dto.ShippingAddressDto;
import com.sieuthithucung.entity.ShippingAddressEntity;
import org.springframework.stereotype.Component;
import tools.jackson.databind.ObjectMapper;

@Component
public class ShippingAddressMapper extends BaseMapper<ShippingAddressEntity, ShippingAddressDto> {

    public ShippingAddressMapper(ObjectMapper objectMapper) {
        super(objectMapper, ShippingAddressEntity.class, ShippingAddressDto.class);
    }
}

